<?php
namespace Alma\Tournament;
use Documents\TournamentChallenge,
    Documents\TournamentChallenger,
    Documents\Tournament,
    Documents\TournamentPayment,
    Documents\TournamentRegistration,
    ObjectValues\TournamentStates,
    ObjectValues\TournamentRegistrationModes,
    ObjectValues\TournamentTypes,
    ObjectValues\TournamentPaymentTypes,
    ObjectValues\TournamentRules,
    ObjectValues\ChallengeStates,
    ObjectValues\VTransactionTypes,
    Services\VBankService;

class RegistrationHelper
{

    protected static $_messages = array(
            'closed' => 'subscriptions closed',
            'bank'   => 'user can not lock money',
            'participating' => 'already participating',
            );

    /**
     * @param int   $userId
     * @param array $amount
     * @param \Documents\TournamentRegistration $registration
     *
     * @return bool
     * TODO: move on registration creation
     */
    protected static function _lock($orm, $userId, array $amount, TournamentRegistration $registration){
        /** @var $bankAccount \Entities\VBankAccount */
        $bankAccount = $orm->getRepository('Entities\VBankAccount')->find($userId);
        $result = false;
        if($bankAccount instanceof \Entities\VBankAccount){
            $bankService = new VBankService($bankAccount, $orm);
            $lockResult = $bankService->createLock($amount,
                array('cause_type' => VTransactionTypes::TOURNAMENT, 'cause_object_id' => $registration->getId()));
            if ($lockResult['status']) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param array $userIds
     * @param \Documents\TournamentRegistration $registration
     */
    protected function _cancelLocks($orm, array $userIds, TournamentRegistration $registration){
        /** @var $bankAccounts  \Entities\VBankAccount[]*/
        $bankAccounts = $orm->createQuery('SELECT b FROM Entities\VBankAccount b WHERE b.id IN('.implode(',',$userIds).')')
            ->getResult();

        foreach($bankAccounts as $bankAccount){
            $bankService = new VBankService($bankAccount, $orm);
            $result = $bankService->cancelLock( array('cause_type' => VTransactionTypes::TOURNAMENT, 'cause_object_id' => $registration->getId()));
        }
    }


    /**
     * subscribe 
     * 
     * @param mixed $orm 
     * @param TournamentRegistration $registration 
     * @param integer $playerId 
     * @return array
     */
    public static function subscribe($orm, TournamentRegistration $registration, $playerId) {
        $error = false;

        // Check if it's to late to register
        $now = new \DateTime();
        if($registration->getEndedAt() && $now > $registration->getEndedAt()) $error = 'closed';
        elseif(in_array($registration->getStatus(), array(TournamentRegistrationModes::CLOSED, TournamentRegistrationModes::UNSUBSCRIPTION_ONLY))) $error = 'closed';
        // Check if player is already registered
        elseif($registration->getPayment($playerId)) $error = 'participating';
        // else subscribe player + lock his money
        else {
            $lockResult = self::_lock($orm, $playerId, array('total' => $registration->getPrice(), 'commission' => $registration->getFee()), $registration);
            if ($lockResult) {
                $payment = new TournamentPayment();
                $payment->fromArray(array(
                            'player_id' => $playerId, 
                            'type' => TournamentPaymentTypes::MONEY, 
                            'amount' => $registration->getPrice()));
                $registration->addPayment($payment);
            } else $error = 'bank';
        }

        return $error ? array('status' => false, 'msg' => self::$_messages[$error], 'error' => $error) : array('status' => true);
    }

    /**
     * unsubscribe 
     * 
     * @param mixed $orm 
     * @param TournamentRegistration $registration 
     * @param integer $playerId 
     * @return array
     */
    public static function unsubscribe($orm, TournamentRegistration $registration, $playerId) {
        $error = false;
        if(!$registration->getPayment($playerId)) $error = 'not_participating';
        elseif(in_array($registration->getStatus(), array(TournamentRegistrationModes::CLOSED, TournamentRegistrationModes::SUBSCRIPTION_ONLY))) $error = 'closed';
        else {
            $lockResult = self::_cancelLocks($orm, array($playerId), $registration);
            if (!$lockResult) $error = 'bank';
        }
        return $error ? array('status' => false, 'msg' => self::$_messages[$error], 'error' => $error) : array('status' => true);
    }

}
