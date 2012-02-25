<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 *	PLAYER MODEL CLASS.
 *
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
 */
require_once(dirname(dirname(__FILE__)).'/models/base_ootp_model.php');
class Players_model extends Base_ootp_model {

    /*--------------------------------
     /	VARIABLES
     /-------------------------------*/
    /**
     *	SLUG.
     *	@var $_NAME:Text
     */
    var $_NAME = 'player_model';
    protected $table        = 'players';
    protected $tables        = array();
    protected $key          = 'player_id';
    protected $soft_deletes = false;
    protected $date_format  = 'datetime';
    protected $set_created  = false;
    protected $set_modified = false;
    /**
     *	PLAYER ID.
     *	@var $player_id:Int
     */
    var $player_id = -1;
    /**
     *	PLAYER ROSTER STATUS.
     *	@var $player_status:Int
     */
    var $player_status = -1;
    /*---------------------------------------------
     /
     /	C'TOR
     /	Creates a new instance of player_model
     /
     /---------------------------------------------*/
    public function __construct() {
        parent::__construct();
        $this->load->database('default');
    }
    /*--------------------------------------------------
     /
     /	PUBLIC FUNCTIONS
     /
     /-------------------------------------------------*/
    // SPECIAL QUERIES
    /**
     * 	GET PLAYER CONUNT.
     *	Test function to assure that players have been imported from the OOTP players file
     * 	into the fantasy database.
     *
     *	@since	1.0.3
     */
    public function getPlayerCount() {
        $this->db->dbprefix = '';
        $this->db->select('player_id');
        $this->db->from($this->table);
        $count = $this->db->count_all_results();
        $this->db->dbprefix = $this->dbprefix;
        return $count;
    }
    public function getPlayerDetails($player_id = false) {

        if ($player_id === false) { return; }
        $details = array();
        $oldprefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        $this->db->select('players.player_id,first_name,last_name,players.nick_name as playerNickname,teams.team_id, teams.name AS team_name, teams.nickName as teamNickname, position,role, date_of_birth,weight,height,bats,throws,draft_year,draft_round,draft_pick,draft_team_id,retired,
						  injury_is_injured, injury_dtd_injury, injury_career_ending, injury_dl_left, injury_left, injury_id, logo_file, players.city_of_birth_id, age');
        //$this->db->join('players','players.player_id = fantasy_players.player_id','left');
        $this->db->join('teams','teams.team_id = players.team_id','right outer');
        $this->db->where('players.player_id',$player_id);
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            $details = $query->row_array();
        }
        $query->free_result();
        /*if (sizeof($details) > 0) {
              $birthCity = '';
              $birthRegion = '';
              $birthNation = '';

              if (isset($details['city_of_birth_id']) && $details['city_of_birth_id'] != 0) {
                  $this->db->select('cities.name as birthCity, cities.region as birthRegion, nations.short_name as birthNation');
                  $this->db->join('nations','nations.nation_id = cities.nation_id','right outer');
                  $this->db->where('cities.city_id',$details['city_of_birth_id']);
                  $cQuery = $this->db->get('cities');
                  if ($cQuery->num_rows() > 0) {
                      $cRow = $cQuery->row();
                      $birthCity = $cRow->birthCity;
                      $birthRegion = $cRow->birthRegion;
                      $birthNation = $cRow->birthNation;
                  }
                  $cQuery->free_result();
              } else {
                  $birthCity = 'Unknown';
                  $birthNation = 'N/A';
              }
              $details = $details + array('birthCity'=>$birthCity,'birthRegion'=>$birthRegion,'birthNation'=>$birthNation);
              $query->free_result();
          } else {
              $details['id'] = $details['player_id'] = -1;
              $details['first_name'] = "Not";
              $details['last_name'] = "Found";
          }*/
        //print($this->db->last_query()."<br />");
        $this->db->dbprefix = $oldprefix;
        return $details;
    }
    public function getPlayersDetails($players = array()) {

        if (sizeof($players) == 0) { return; }
        $playersInfo = array();

        foreach($players as $row) {
            $playersInfo = $playersInfo + array($row['player_id'] => $this->getPlayerDetails($row['player_id']));
        }
        //echo($this->db->last_query()."<br />");
        return $playersInfo;
    }

    public function getPlayerName($player_id = false) {

        if ($player_id === false) { $player_id = $this->player_id; }

        $name = "";
        $oldprefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        // GET PLAYER POSITION
        $this->db->select('first_name, last_name');
        //$this->db->join("players","fantasy_players.player_id = players.player_id", "right outer");
        $this->db->where('id',$player_id);
        $query = $this->db->get($this->table);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $name = $row->first_name." ".$row->last_name;
        }
        $query->free_result();
        $this->db->dbprefix = $oldprefix;
        return $name;

    }
    public function getPlayerPosition($player_id = false) {

        if ($player_id === false) { $player_id = $this->player_id; }

        $pos = -1;
        $oldprefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        // GET PLAYER POSITION
        $this->db->select('position');
        $this->db->from($this->table);
        $this->db->where('player_id',$player_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $pos = $row->position;
        }
        $query->free_result();
        $this->db->dbprefix = $oldprefix;
        return $pos;

    }
    public function getTeam($player_id = false) {

        if ($player_id === false) { $player_id = $this->player_id; }

        $team_id = -1;
        // GET PLAYER POSITION
        $this->db->dbprefix = '';
        $this->db->select('team_id');
        $this->db->where('player_id',$player_id);
        $query = $this->db->get($this->table);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $team_id = $row->team_id;
        }
        $query->free_result();
        $this->db->dbprefix = $this->dbprefix;
        return $team_id;

    }

    public function getActivePlayers($league_id = false,$searchType = 'all', $searchParam = false,$current_scoring_period = 1, $nonFreeAgents = array()) {
        return $this->getPlayers($league_id,$searchType, $searchParam,$current_scoring_period, $nonFreeAgents,1);
    }
    public function getPlayers($league_id = false,$searchType = 'all', $searchParam = false,$current_scoring_period = 1, $nonFreeAgents = array(),
                               $playerStatus = false, $selectBox = false) {
        $players = array();
        $this->db->dbprefix = '';
        $this->db->select('players.player_id, first_name, last_name, players.position, players.role, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, ');

        //$this->db->join('players','players.player_id = fantasy_players.player_id','left');
        switch ($searchType) {
            case 'alpha':
                $this->db->like('players.last_name', $searchParam, 'after');
                break;
            case 'pos':
                $col = "position";
                if ($searchParam == 11 || $searchParam == 12 || $searchParam == 13) {
                    $col = "role";
                }
                if ($searchParam == 20) {
                    $this->db->where('(players.position = 7 OR players.position = 8 OR players.position = 9)');
                } else if ($searchParam == 12 || $searchParam == 13) {
                    $this->db->where('(players.role = 12 OR players.role = 13)');
                } else {
                    $this->db->where('players.'.$col, $searchParam);
                }
                break;
            case 'all':
            default:
                break;
        } // END switch
        $this->db->where('players.retired',0);
        $this->db->order_by('players.last_name','asc');
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            $fields = $query->list_fields();
            if ($selectBox === true) {
                $players = array(-1=>"Select Player");
            }
            foreach ($query->result() as $row) {
                $tmpPos = "";
                if ($row->position == 1) {
                    $tmpPos = $row->role;
                } else {
                    $tmpPos = $row->position;
                }
                if ($selectBox === false) {
                    $player = array();
                    foreach($fields as $field) {
                        $player[$field] = $row->$field;
                    }
                    $player['player_name'] = $row->first_name." ".$row->last_name;
                    $player['pos'] = $tmpPos;
                    array_push($players,$player);
                } else {
                    $players = $players + array($row->id=>$row->last_name.", ".$row->first_name." - ".get_pos($tmpPos));
                }
            }
        }
        $this->db->dbprefix = $this->dbprefix;
        return $players;
    }

    /**
     *	GET PLAYER STATS FOR PERIOD
     *	Returns player stats from OOTP game data based on a fixed time period or a year.
     *	@since	1.0.1.9
     *	@param	$playerType			1= Batters, 2 = Pitchers
     *	@parsm	$scoring_period		Scoring Period Array object
     *	@param	$rules				Scoring Rules Array
     *	@param	$players			Players List Array, generated by TeamModel->GetBatters() or TeamModel->getPitchers()
     *	@param	$excludeList		Array of Player Ids to exclude
     *	@param	$batting_sort		Batting Stats sort column
     *	@param	$pitching_sort 		Pitching Stats sort column
     *	@return						Stats array object
     *	@see	TeamModel
     *
     */
    public function getStatsforPeriod($playerType = 1, $scoring_period = array(), $rules = array(),
                                      $players = array(),$excludeList = array(), $searchType = 'all', $searchParam = false,
                                      $query_type = QUERY_STANDARD, $stats_range = -1, $limit = -1, $startIndex = 0,
                                      $batting_sort = false, $pitching_sort = false) {
        $stats = array();

        $playerList = "(";
        if (is_array($players) && sizeof($players) > 0) {
            foreach($players as $player_id => $playerData) {
                if ($playerList != "(") { $playerList .= ","; }
                $playerList .= $player_id;
            }
        }
        $playerList .= ")";

        $excludeLostStr = "(";
        if (is_array($excludeList) && sizeof($excludeList) > 0) {
            foreach($excludeList as $player_id) {
                if ($excludeLostStr != "(") { $excludeLostStr .= ","; }
                $excludeLostStr .= $player_id;
            }
        }
        $excludeLostStr .= ")";
        $this->db->dbprefix = '';
        // BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
        $sql = 'SELECT players.player_id, players.position, players.role, players.player_id ,first_name, last_name,players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,rating,';
        $sql .= player_stat_query_builder($playerType, $query_type, $rules);
        if ($playerType == 1) {
            $sql .= ",players.position as pos ";
            $tblName = 'players_game_batting';
            $posType = 'players.position';
            if ($batting_sort !== false) $order = $batting_sort;
            $order = 'ab';
        } else {
            $sql .= ",players.role as pos ";
            $tblName = 'players_game_pitching_stats';
            $posType = 'players.role';
            if ($pitching_sort !== false) $order = $pitching_sort;
            $order = 'ip';
        }
        $sql .= "FROM games ";
        $sql .= 'LEFT JOIN '.$tblName.' ON games.game_id = '.$tblName.'.game_id ';
        $sql .= 'RIGHT OUTER JOIN players ON players.player_id = '.$tblName.'.player_id ';
        //$sql .= 'RIGHT OUTER JOIN fantasy_players ON players.player_id = fantasy_players.player_id ';
        if (sizeof($rules) > 0 && isset($rules['scoring_type']) && $rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
            $order = 'fpts';
        }
        if (sizeof($scoring_period) > 0 && $stats_range == -1) {
            $sql .= "WHERE DATEDIFF('".$scoring_period['date_start']."',games.date)<= 0 ";
            $sql .= "AND DATEDIFF('".$scoring_period['date_end']."',games.date)>= 0 ";
        } else if (sizeof($scoring_period) == 0 && $stats_range != -1) {
            $year_time = (60*60*24*365);
            if ($stats_range != 4) {
                $sql .= ' AND games.year = '.date('Y',time()-($year_time * $stats_range));
            } else {
                $sql .= ' AND (games.year = '.date('Y',time()-($year_time)).' OR games.year = '.date('Y',time()-($year_time * 2)).' OR games.year = '.date('Y',time()-($year_time * 3)).")";
            }
        }
        switch ($searchType) {
            case 'alpha':
                $sql .= ' AND players.last_name LIKE "'.$searchParam.'%" ';
                break;
            case 'pos':
                $col = "position";
                if ($searchParam == 11 || $searchParam == 12 || $searchParam == 13) {
                    $col = "role";
                }
                if ($searchParam == 20) {
                    $sql .= ' AND (players.position = 7 OR players.position = 8 OR players.position = 9) ';
                } else if ($searchParam == 12 || $searchParam == 13) {
                    $sql .= ' AND (players.role = 12 OR players.role = 13) ';
                } else {
                    $sql .= ' AND players.'.$col.' = '.$searchParam." ";
                }
                break;
            case 'all':
            default:
                break;
        } // END switch
        if ($playerList != "()") {
            $sql .= "AND ".$tblName.".player_id IN ".$playerList.' ';
        }
        if ($excludeLostStr != "()") {
            $sql .= "AND ".$tblName.".player_id NOT IN ".$excludeLostStr.' ';
        }
        $sql .= "GROUP BY ".$tblName.'.player_id ';
        $sql .= "ORDER BY ".$order." DESC ";
        if ($limit != -1 && $startIndex == 0) {
            $sql.="LIMIT ".$limit;
        } else if ($limit != -1 && $startIndex > 0) {
            $sql.="LIMIT ".$startIndex.", ".$limit;
        }
        $gQuery = $this->db->query($sql);
        //echo($sql."<br />");
        if ($gQuery->num_rows() > 0) {
            $fields = $gQuery->list_fields();
            foreach ($gQuery->result() as $sRow) {
                $player = array();
                foreach($fields as $field) {
                    $player[$field] = $sRow->$field;
                }
                $player['player_name'] = $sRow->first_name." ".$sRow->last_name;
                if ($sRow->position == 1) {
                    $player['pos'] = $sRow->role;
                } else {
                    $player['pos'] = $sRow->position;
                }
                array_push($stats,$player);
            }
        }
        $gQuery->free_result();
        $this->db->dbprefix = $this->dbprefix;
        return $stats;
    }

    /**
     *	UPDATE PLAYER RATINGS.
     *	Loads players tstistical performance for the period in days specififed to compute the average
     *	and standard population deviation values. Then, all players compiled stats for the given
     *	period are loaded and rated based on coparison to these averages and stored in the
     *	fantasy_players table.
     *
     *	@param	$ratingsPeriod		Number of days back to rate stats
     *	@param	$scoring_period		Scoring Period object
     *	@param	$ootp_league_id		OOTP League ID value, defaults to 100 if no value passed
     *	@return						Summary String
     *	@since						1.0.4
     *	@version					1.0
     *	@see						Controller->Admin->playerRatings()
     *
     */
    public function updatePlayerRatings($ratingsPeriod = 15, $scoring_period = false, $ootp_league_id = 100) {
        if (($scoring_period === false|| sizeof($scoring_period) < 1)) { return false; } // END if

        $this->lang->load('admin');
        /*--------------------------------------
          /
          /	1.0 ARRAY PREP
          /
          /-------------------------------------*/
        $error = false;
        /*--------------------------------------
          /	1.1 GET PLAYERS ARRAY
          /-------------------------------------*/
        $player_list = $this->getActivePlayers();
        $summary = $this->lang->line('sim_player_ratings');
        /*--------------------------------------
          /	1.2 DEFINE RATING PERIOD
          /-------------------------------------*/
        $day = 60*60*24;
        $period_start = date('Y-m-d',((strtotime($scoring_period['date_end']))-($day*$ratingsPeriod)));
        $statsTypes = array(1=>'batting',2=>'pitching');
        $statCats = array();
        $ratingsCats = array();

        $period_str = str_replace('[START_DATE]',$period_start,$this->lang->line('sim_player_rating_period'));
        $period_str = str_replace('[END_DATE]',$scoring_period['date_end'],$period_str);
        $summary .= str_replace('[DAYS]',$ratingsPeriod,$period_str);
        /*--------------------------------------
          /
          /	2.0 STAT AVG,STDDEV LOOP
          /
          /-------------------------------------*/
        if (sizeof($player_list) > 0) {
            $summary .= str_replace('[PLAYER_COUNT]',sizeof($player_list),$this->lang->line('sim_player_rating_count'));
            $processCount = 0;
            /*-------------------------------------------
               /	2.1 BUOLD LIST OF ACTIVE PLAYERS
               /-------------------------------------------*/
            $players_str = "(";
            foreach($player_list as $row) {
                if ($players_str != "(") { $players_str .= ","; }
                $players_str .= $row['player_id'];
            }
            $players_str .= ")";
            /*-------------------------------
               /	2.2 SWITCH ON PLAYER TYPE
               /------------------------------*/
            $statTotals = array(1=>array(),2=>array());
            $statSummaries = array(1=>array(),2=>array());
            $summary .= $this->lang->line('sim_player_rating_statload');

            foreach ($statsTypes as $typeId => $type) {
                if ($typeId == 1) {
                    $table = "players_game_batting";
                    $qualifier = "ab";
                    $minQualify = 3.1;
                } else {
                    $table = "players_game_pitching_stats";
                    $qualifier = "ip";
                    $minQualify = 1;
                } // END if
                /*-------------------------------
                    /	2.2.1 INDIVIDUAL STAT LOOP
                    /------------------------------*/
                // BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
                $ratingsCats = $ratingsCats + array($typeId => get_stats_for_ratings($typeId));
                $localStats = array();
                $statSum = "";
                foreach($ratingsCats[$typeId] as $id => $val) {
                    $statSum .= "<b>Stat = ".$val."</b><br />";
                    $tmpSelect = 'games.date, ';
                    $id = intval($id);
                    $stat = '';
                    // FILTER OUT COMPILED STATS LIKE AVG, ERA AND WHIP
                    switch($typeId) {
                        case 1:
                            if ($id <= 17 || $id >= 26) {
                                $stat = strtolower(get_ll_cat($id, true));
                            } // END if
                            break;
                        case 2:
                            if ($id <= 39 || $id >= 43) {
                                $stat = strtolower(get_ll_cat($id, true));
                            } // END if
                            break;
                        default:
                            break;
                    } // END switch
                    if (!empty($stat)) { $tmpSelect .= 'SUM(g) as sum_g, SUM('.$stat.') as sum_'.$stat.', SUM('.$qualifier.') as sum_'.$qualifier; }
                    /*-----------------------------------------
                         /	2.2.1.1 EXECUTE THE QUERY FOR THIS STAT
                         /----------------------------------------*/
                    $this->db->dbprefix = '';
                    $this->db->flush_cache();
                    $this->db->select($tmpSelect);
                    $this->db->join($table,'games.game_id = '.$table.'.game_id','left');
                    $this->db->where($table.'.player_id IN '.$players_str);
                    $this->db->where("DATEDIFF('".$period_start."',games.date)<=",0);
                    $this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
                    $this->db->group_by($table.'.player_id');
                    $this->db->order_by($table.'.player_id', 'asc');
                    $query = $this->db->get($this->tables['OOTP_GAMES']);
                    //echo($this->db->last_query()."<br />");
                    if ($query->num_rows() > 0) {
                        $statCount = 0;
                        $statTotal = 0;
                        $statStr = 'sum_'.$stat;
                        $statQalifier = 'sum_'.$qualifier;
                        $statArr = array();
                        foreach($query->result() as $row) {
                            if (($row->$statQalifier / $row->sum_g) > $minQualify) {
                                array_push($statArr,$row->$statStr);
                            }
                        }
                        $statAvg = average($statArr);
                        $statSum .= $stat." total = ".$statTotal."<br />";
                        $statSum .= $stat." AVG = ".sprintf('%.3f',$statAvg)." (".$statTotal."/".$statCount.")<br />";
                        $stdDevTotal = 0;
                        $statDev = deviation($statArr);
                        if ($statDev < 0) { $statDev = -$statDev; }
                        $statSum .= $stat." STDDEV = ".$statDev."<br />";
                    } // END if
                    $localStats[$stat] = array('avg'=>$statAvg,'stddev'=>$statDev);
                    $query->free_result();
                    $statSum .= $statCount." Player Statistics met the qualified minimum.<br />";
                }
                $statSummaries[$typeId] = $statSum;
                $statTotals[$typeId] = $localStats;
            }
            $statTotalStr = str_replace('[BATTING_STAT_COUNT]',sizeof($statTotals[1]),$this->lang->line('sim_player_rating_statcount'));
            $summary .= str_replace('[PITCHING_STAT_COUNT]',sizeof($statTotals[2]),$statTotalStr);
            $summary .= "Batting Stat Details:<br />".$statSummaries[1];
            $summary .= "Pitching Stat Details:<br />".$statSummaries[2];

            $summary .= $this->lang->line('sim_players_rating_processing');
            foreach($player_list as $row) {
                $playerSum = "";
                if ($row['position'] != 1) {
                    $type = 1;
                    $table = "players_game_batting";
                    $qualifier = "ab";
                } else {
                    $type = 2;
                    $table = "players_game_pitching_stats";
                    $qualifier = "ip";
                } // END if
                $select = $table.'.player_id,SUM('.$qualifier.') as sum_'.$qualifier.',';
                foreach($ratingsCats[$type] as $id => $val) {
                    $stat = "";
                    $id = intval($id);
                    switch($type) {
                        case 1:
                            if ($id <= 17 || $id >= 26) {
                                $tmpStat = strtolower(get_ll_cat($id, true));
                                $stat = "SUM(".$tmpStat.") as sum_".$tmpStat;
                            } // END if
                            break;
                        case 2:
                            if ($id <= 39 || $id >= 43) {
                                $tmpStat = strtolower(get_ll_cat($id, true));
                                $stat = "SUM(".$tmpStat.") as sum_".$tmpStat;
                            } // END if
                            break;
                        default:
                            break;
                    } // END switch
                    if (!empty($stat)) {
                        if ($select != '') { $select.=","; } // END if
                        $select .= $stat;
                    } // END if
                } // END foreach

                $this->db->select($select);
                $this->db->join($table,'games.game_id = '.$table.'.game_id','left');
                $this->db->where($table.'.player_id', $row['player_id']);
                $this->db->where("DATEDIFF('".$period_start."',games.date)<=",0);
                $this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
                $this->db->group_by($table.'.player_id');
                $this->db->order_by($table.'.'.$qualifier,'desc');
                $query = $this->db->get($this->tables['OOTP_GAMES']);
                $statCount = 0;
                $rating = 0;
                if ($query->num_rows() > 0) {
                    $pRow = $query->row();
                    $tmpQulaify = "sum_".$qualifier;
                    // ONLY PROCESS THIS PLAYER IS THERE ARE GOING TO BE STATS TO PROCESS
                    if ($pRow->$tmpQulaify > 0) {
                        foreach($ratingsCats[$type] as $id => $val) {
                            $stat = strtolower(get_ll_cat($id, true));
                            $tmpStat = "sum_".$stat;
                            // SKIP PLAYERS WITH NO APPEARENCES IN PLAY
                            $negative = false;
                            if (($type == 1 && $id == 4) || ($type == 2 && $id == 36) || ($type == 2 && $id == 37)) {
                                $negative = true;
                            }
                            $rawRating = $pRow->$tmpStat - $statTotals[$type][$stat]['avg'];
                            if ($statTotals[$type][$stat]['stddev'] != 0) {
                                $upRating = $rawRating / $statTotals[$type][$stat]['stddev'];
                                //print("rawRating /stdev = ".$upRating." (".$rawRating." / ".$statTotals[$type][$stat]['stddev'].")<br />");
                            } else {
                                $upRating = $rawRating;
                            }
                            if ($negative) {
                                $rating -= $upRating;
                            } else {
                                $rating += $upRating;
                            }
                            $statCount++;
                        }
                    }
                }
                $query->free_result();
                // GET THE AVERAGE OVERALL RATING
                //if ($rating != 0 && $statCount != 0) {
                //	$rating = $rating / $statCount;
                //}
                // SAVE THE UPDATED RATING
                $this->db->flush_cache();
                $data = array('rating'=>$rating);
                $this->db->where('player_id',$row['player_id']);
                $this->db->update($this->tblName,$data);
                $processCount++;
            }
            $result = 1;
            $summary .= str_replace('[PLAYER_COUNT]',$processCount,$this->lang->line('sim_players_rating_result'));

        } else {
            $result = -1;
            $summary .= $this->lang->line('sim_players_rating_no_players');
        }
        //print("<br />".$summary."<br />");
        $this->db->dbprefix = $this->dbprefix;
        return array($result,$summary);
    }

    /**
     *	GET CAREER STATS.
     *	Returns players career statistics.
     *
     *	@param	$ootp_league_id		OOTP League ID value
     *	@param	$player_id			Player Id, defaults to current player id if empty
     *	@return						Stat Array
     *	@since						1.0
     *	@version					1.0.2
     *
     */
    public function getCareerStats($ootp_league_id, $player_id = false) {
        if ($player_id === false) { $player_id = $this->player_id; }

        $career_stats = array();
        // GET PLAYER POSITION
        $pos = $this->getPlayerPosition();
        $this->db->dbprefix = '';
        $this->db->flush_cache();
        if ($pos == 1) {
            $sql="SELECT pcp.year,pcp.team_id,g,gs,w,l,s,(ip*3+ipf)/3 as ip,ha,r,er,hra,bb,k,hld,cg,sho,ab,sf,vorp";
            $sql.=",bf,pi,qs,gf,gb,fb,wp,bk,svo,bs";     ## Expanded Stats
            $sql.=" FROM players_career_pitching_stats as pcp WHERE player_id=$player_id";
            $sql.=" AND league_id=$ootp_league_id AND split_id=1";
            $sql.=" ORDER BY pcp.year;";
        } else {
            $sql="SELECT pcb.year,pcb.team_id,g,ab,h,d,t,hr,rbi,r,bb,hp,sh,sf,k,sb,cs,pa,vorp";
            $sql.=",pitches_seen,ibb,gdp";
            $sql.=" FROM players_career_batting_stats as pcb WHERE player_id=$player_id";
            $sql.=" AND league_id=$ootp_league_id AND split_id=1";
            $sql.=" ORDER BY pcb.year;";
        } // END if
        $query = $this->db->query($sql);
        if ($query->num_rows > 0) {
            $fields = $query->list_fields();
            foreach($query->result() as $row) {
                $year = array();
                foreach($fields as $field) {
                    $year[$field] = $row->$field;
                } // END foreach
                array_push($career_stats,$year);
            } // END foreach
        } // END if
        $query->free_result();
        $this->db->dbprefix = $this->dbprefix;
        return $career_stats;
    }
    /**
     *	GET PLAYER AWARDS.
     *	Returns all awards won by the players broken out by award type.
     *
     *	@param	$ootp_league_id		OOTP League ID value
     *	@param	$player_id			Player Id, defaults to current player id if empty
     *	@return						Award Array
     *	@since						1.0
     *	@version					1.0.1
     *
     */
    public function getPlayerAwards($ootp_league_id, $player_id = false) {

        if ($player_id === false) { $player_id = $this->player_id; }

        $awards = array();
        $this->db->dbprefix = '';
        $this->db->select("award_id,year,position");
        $this->db->from('players_awards');
        $this->db->where('league_id',$ootp_league_id);
        $this->db->where('player_id',$player_id);
        $this->db->where_in('award_id',array(4,5,6,7,9));
        $this->db->order_by('award_id','award_id,year,position');
        $query = $this->db->get();
        $prevAW=-1;
        $cnt=0;
        if ($query->num_rows > 0) {
            $awardsByYear = array();
            $poy = array();
            $boy = array();
            $roy = array();
            $gg = array();
            $as = array();
            foreach($query->result_array() as $row) {
                $awid=$row['award_id'];
                $yr=$row['year'];
                $pos=$row['position'];
                if ($prevAW!=$awid) {
                    $awardsByYear[$awid]=$yr;
                } else {
                    $awardsByYear[$awid]=$awardsByYear[$awid].", ".$yr;
                } // END if

                switch ($awid) {
                    case 4: $poy[$yr]=1; break;
                    case 5: $boy[$yr]=1; break;
                    case 6: $roy[$yr]=1; break;
                    case 7: $gg[$yr][$pos]=1; break;
                    case 9: $as[$yr]=1; break;
                } // END switch
                $cnt++;
                $prevAW=$awid;


            } // END foreach
            $awards['byYear'] = $awardsByYear;
            $awards['poy'] = $poy;
            $awards['boy'] = $boy;
            $awards['roy'] = $roy;
            $awards['gg'] = $gg;
            $awards['as'] = $as;
        } // END if
        $this->db->dbprefix = $this->dbprefix;
        return $awards;
    }
    public function getRecentGameStats($ootp_league_id, $last_date, $lgyear, $days = 7, $player_id = false) {

        if ($player_id === false) { $player_id = $this->player_id; }

        // GET ALL TEAMS
        $this->db->dbprefix = '';
        $teams = array();
        $this->db->select("team_id, abbr");
        $this->db->where("league_id",$ootp_league_id);
        $query = $this->db->get("teams");
        if ($query->num_rows() > 0) {
            foreach($query->result() as $row) {
                $teams[$row->team_id] = $row->abbr;
            }
        }
        $query->free_result();

        $stats = array();
        $pos = 0;
        $team_id = $this->getTeam();
        // GET PLAYER POSITION
        $pos = $this->getPlayerPosition();

        $select = "games.date,games.home_team,games.away_team,";
        $this->db->flush_cache();
        if ($pos == 1) {
            $this->db->select($select.'w,l,s,ip,ha,er,bb,k');
            $table = 'players_game_pitching_stats';
        } else {
            $this->db->select($select.'ab,r,h,hr,rbi,bb,sb');
            $table = 'players_game_batting';
        }
        $this->db->from($table);
        $this->db->join($this->tables['OOTP_GAMES'],$table.".game_id = games.game_id",'left');
        $this->db->where($table.'.player_id',$player_id);
        $this->db->where($table.'.year',$lgyear);
        $this->db->where($table.'.level_id',1);
        $this->db->where('games.game_type',0);
        $this->db->where("(games.home_team = ".$team_id." OR games.away_team = ".$team_id.")");
        $this->db->where("DATEDIFF('".$last_date."',games.date) > ",0);
        $this->db->order_by("games.date",'desc');
        $query = $this->db->get();
        $fields = $query->list_fields();
        if ($query->num_rows() > 0) {
            $count = 0;
            foreach($query->result() as $row) {
                $game = array();
                foreach($fields as $field) {
                    if ($field != 'home_team' && $field != 'away_team') {
                        $game[$field] = $row->$field;
                    }
                }
                if ($row->home_team == $team_id) {
                    if (isset($teams[$row->away_team])) {
                        $game['opp'] = $teams[$row->away_team];
                    } else {
                        $game['opp'] = "?";
                    }
                } else if ($row->away_team == $team_id) {
                    if (isset($teams[$row->home_team])) {
                        $game['opp'] = $teams[$row->home_team];
                    } else {
                        $game['opp'] = "?";
                    }
                } else {
                    $game['opp'] = "?";
                }
                array_push($stats,$game);
                $count++;
                if ($count >= $days) break;
            }
        }
        $this->db->dbprefix = $this->dbprefix;
        return $stats;

    }
    public function getCurrentStats($ootp_league_id, $lgyear, $player_id = false) {
        if ($player_id === false) { $player_id = $this->player_id; }

        $stats = array();
        $pos = 0;
        // GET PLAYER POSITION
        $pos = $this->getPlayerPosition($player_id);
        //print($this->db->last_query()."<br />");
        $this->db->dbprefix = '';
        $this->db->flush_cache();
        if ($pos == 1) {
            $this->db->select('ip,w,l,s,k,bb,er,ha,hra');
            $this->db->from('players_career_pitching_stats');
        } else {
            $this->db->select('ab,r,hr,rbi,bb,k,sb,h,d,t');
            $this->db->from('players_career_batting_stats');
        }
        $this->db->where('player_id',$player_id);
        $this->db->where('split_id',1);
        $this->db->where('year',$lgyear);
        $this->db->where('league_id',$ootp_league_id);
        $this->db->where('level_id',1);
        $query = $this->db->get();
        $fields = $query->list_fields();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            foreach($fields as $field) {
                $stats[$field] = $row->$field;
            }
        }
        $this->db->dbprefix = $this->dbprefix;
        return $stats;
    }
}  