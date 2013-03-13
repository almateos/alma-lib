<?php
namespace Alma;
use Documents\Challenge,
    Documents\Challenger,
    Documents\Tournament,
    Documents\TournamentParticipant,
    ObjectValues\TournamentStates,
    ObjectValues\TournamentTypes,
    ObjectValues\TournamentRules,
    ObjectValues\ChallengeStates;

class TournamentBuilder
{
    /**
     * @param $rounds
     *
     * @return array
     */
    // TODO: Prize distribution calculation is variable dependanding tounament type, it should be in some tournamentType's lib
    protected static function _calculatePrizeDistribution($rounds){

        $repartition = array(
            array(100),
            array(70, 30),
            array(70, 30),
            array(45, 25, 15),
            array(35, 20, 10, 6.25),
            array(30, 18, 10, 4, 2),
            array(27, 15, 8, 3.5, 1.5, 1),
            array(24.5, 12.74, 6.9, 3.2, 1.2, 0.8, 0.43),
            array(22.5, 11.5, 6, 2.6, 0.97, 0.66, 0.35, 0.22),
            array(20, 10.44, 5.1, 3, 0.76, 0.55, 0.3, 0.2, 0.11)
        );
        return $repartition[$rounds - 1];
    }

    protected static function _checkRequirements($participants, array $rules) {
        $msg = false;
        
        foreach($participants as $participant) {
            if(!($participant instanceof TournamentParticipant)) {
                $msg = 'Invalid argument type, TournamentBuilder::create() takes as second parameter an array of TournamentParticipant';
                break;
            }
        }

        if(array_key_exists(TournamentRules::TOTAL_PLAYER_NUM, $rules)) {
            $playerNum = $rules[TournamentRules::TOTAL_PLAYER_NUM];
            if(is_array($playerNum)) {
                if(array_key_exists('min', $playerNum) && $playerNum['min'] > count($participants)) $msg = 'Minimum participant number not reached, tournament has not been created';
                if(array_key_exists('max', $playerNum) && $playerNum['max'] < count($participants)) $msg = 'Maximum participant number is exceeded, tournament has not been created';
            } else {
                if((int) $playerNum !== count($participants)) $msg = 'Invalid participant number, ' . $playerNum . ' expected, ' . count($participants) . ' given';
            }
        } 

            //$this->_cancelTournament(array('error'=> 'not enough participants'));
        $result = $msg ? array('status' => false, 'msg' => $msg) : array('status' => true);
        return $result;
    }

    protected static function _createChallengers($participants) {
        //create challengers;
        $challengers = array();
        //$participants = array();
        foreach($participants as $participant){
            $participants[] = $participant;
            $challenger = new Challenger();
            $challenger->fromArray(array(
                       'status'     => \ObjectValues\ChallengerStates::INITIAL,
                       'player_id'  => $participant->getPlayerId()
                       ));
            $challengers[] = $challenger;
        }
        return $challengers;
    }

    protected static function _createChallenges($nbRounds) {
        //calculate rounds;

        //TODO: We are a bit short in time now, but round should be calculated out of rules, example: TournamentRules::getConfig(TournamentTypes::XvsX)



        //create challenges tree
        $challenges = array();

        for($i = $nbRounds; $i > 0; $i--) {
            $challengesToCreate = pow(2, $i-1);
            $roundNumber = $nbRounds - $i + 1;

            while($challengesToCreate > 0) {
                $challenge = new Challenge();
                $challenge->fromArray(array(
                            'round'         => $roundNumber,
                            'status'        => ChallengeStates::INITIAL,
                            'position'      => $challengesToCreate,
                            //->setPosition($tournament->getType() == TournamentValues::TYPE_1VS1 ? 0 : $challengesToCreate)
                            //'tournament_id' => $tournament->getId(),
                            ));
                $challengesToCreate--;
                //store round's #1 challenges, to assign players later
                if($roundNumber == 1) $challenges[] = $challenge;
            }
        }
        return $challenges;
    }


    protected function _startChallenges() {

            // launch round #1 challenges
            foreach($firstRoundChallenges as $challenge){
                if( count($challenge->getChallengers()) == 1){
                    $challenge->setStartedAt(new \DateTime())
                        ->setFinishedAt(new \DateTime())
                        ->setStatus(ChallengeStates::FINISHED);
                    foreach($challenge->getChallengers() as $challenger){
                        $challenger->setStatus(ChallengerStates::WON);
                    }
                } else {
                    $challenge->setStartedAt(new \DateTime())
                        ->setStatus( ChallengeStates::READY );
                }
            }

            $this->odm->flush(array('safe' => true, 'fsync' => true));
            foreach($firstRoundChallenges as $challenge){
                if ($challenge->getStatus() == ChallengeStates::FINISHED) {
                    $this->finishChallenge($challenge);
                }
            }
            $this->events->trigger('tournament_started', $this, $this->_params);
    }

    /**
     * create 
     * 
     * @param array $game 
     * @param array $playerIds 
     * @param int $type 
     * @param array $options 
     * @static
     * @return void
     */
    public static function create(array $game, array $participants, $type, array $options = array()) {
        $rules = array_merge($options, TournamentRules::getConfig($type));
        $result = self::_checkRequirements($participants, $rules);
        if($result['status']) {

            $nbRounds = strlen(decbin(count(participants)) -1);
            $challenges  = self::_createChallenges($nbRounds);
            $challengers = self::_createChallengers($participants);


            shuffle($challengers);
            $i = 0;
            while(!empty($challengers)) {
                $challenger = array_pop($challengers);
                $challenges[$i]->addChallenger($challenger);
                $challenges[$i]->setStatus(ChallengeStates::READY);

                if (++$i >= count($firstRoundChallenges)) $i = 0;
            }


            $tournament = new \Documents\Tournament();
            $tournament->fromArray(array(
                        'game' => $game,
                        'type' => $type,
                        'participants' => $participants,
                        'challenges' => $challenges,
                        ));
            //$tournament->setRule('rounds', $nbRounds );
            $tournament->setRounds($nbRounds);

            // distribution
            // TODO: should be an optional rule
            $tournament->setRule('distribution', self::_calculatePrizeDistribution($tournament->getRule('rounds')));

            //set tournament as launched
            return $tournament;
        } else {
            throw new \Exception($result['msg']);
        }
    }
}
