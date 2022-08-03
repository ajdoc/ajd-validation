<?php 

private function _checkGroup( array $data, $middleware = FALSE )
	{
		static::$ajd_prop['check_group'] 	= TRUE;
		
		$value 								= NULL;
		$or_success 						= array();

		$or_pass_arr 						= array();
		$or_passed_per_pass 				= [];
		$or_passed_per_single_pass 			= [];

		$obs           	 					= static::get_observable_instance();
		$ev									= static::get_promise_validator_instance();

		$and_check 							= array();
		$or_check 							= array();

		$validator 							= $this->getValidator();
		$paramValidator 					= $validator->one_or( Validator::contains('.'), Validator::contains('*') );

		$orPromises = [];
		$andPromises = [];

		if( ISSET( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ] ) )
		{

			if( !EMPTY( static::$useContraintGroup ) )
			{
				$or_field 						= static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_OR ];
			}
			else
			{
				$or_field 						= static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ];
			}
			
			if( !EMPTY( $or_field ) )
			{
				$fk = 0;
				foreach( $or_field as $field_key => $field_value )
				{

					$fieldValueOr 	= array();

					$realFieldKey 	= Validation_helpers::getParentPath($field_key);
					
					if( ISSET( $field_value[Abstract_common::LOG_OR] ) )
					{
						$fieldValueOr 	= $field_value[Abstract_common::LOG_OR];
					}

					$propScene 			= $this->clearScenario( $field_value[Abstract_common::LOG_AND], $fieldValueOr );

					$field_value[Abstract_common::LOG_AND] 	= $propScene['prop_and'];
					$field_value[Abstract_common::LOG_OR] 	= $propScene['prop_or'];

					if( ISSET( $data[ $realFieldKey ] ) ) 
					{
						$value 				= $data[ $realFieldKey ];
					}
					else 
					{
						$value 				= '';
					}
					
					if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) OR ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
					{
						$and_search = array();
						$or_search 	= array();

						if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) )
						{
							$and_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_AND]['scenarios'] );
						}

						if( ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
						{
							$or_search 	= $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_OR]['scenarios'] );
						}

						if( !EMPTY( $and_search ) OR !EMPTY( $or_search ) )
						{
							break;
						}
					}

					if( $paramValidator->validate($field_key) )
					{
						$field_key 		= Validation_helpers::removeParentPath( $realFieldKey, $field_key );
					}
 					
 					$or_pass_arr = [];
 					$orResultArr = [];
 					$or_passed_per = [];

 					/*if(!empty(static::$ajd_prop['groupings']))
					{
						static::$ajd_prop['cache_groupings'] = static::$ajd_prop['groupings'];
					}*/

					$or 					 = $this->checkArr( $field_key, $value, array(), TRUE, Abstract_common::LOG_OR, $field_value,  
						function($ajd, $checkResult) use (&$or_pass_arr, &$orResultArr, &$or_passed_per)
						{
							if(!empty($checkResult))
							{
								$orResultArr = $checkResult;

								if(isset($checkResult[Abstract_common::LOG_AND]['passed_per']))
								{
									$or_passed_per['passed_per'] = $checkResult[Abstract_common::LOG_AND]['passed_per'];
								}

								if(isset($checkResult[Abstract_common::LOG_AND]['auto_arr_result']))
								{
									$or_passed_per['auto_arr_result'] = $checkResult[Abstract_common::LOG_AND]['auto_arr_result'];
								}
								
								if(!empty($checkResult[ Abstract_common::LOG_AND ]))
								{
									if(isset($checkResult[Abstract_common::LOG_AND]['pass_arr']))
									{
										$or_pass_arr['pass_arr'] = $checkResult[ Abstract_common::LOG_AND ]['pass_arr'];
									}

									if(isset($checkResult[Abstract_common::LOG_AND]['sequence_check']))
									{
										$or_pass_arr['sequence_check'] = $checkResult[ Abstract_common::LOG_AND ]['sequence_check'];
									}

								}
							}
							
						},
						false
					);

					/*if(is_array($value))
					{
						$this->processFieldRulesSequence();
					}*/

					$or_field_ch 			= $this->validation_fails( $field_key, null, true );
					$or_field_ch_orig 		= $this->validation_fails( $field_key );
					
					if($or_field_ch)
					{						
						$or->then(function()
						{
							throw new Exception('Validation Failed.');
							
						});
						
						$orFailed 	= PromiseHelpers::reject($or);
						
						$orPromises[] 			= $orFailed;	
					}
					else
					{
						$orPromises[] 			= $or;	
					}

					$or_check[] 			= $or_field_ch_orig;

					$cnt 					 = 0;
					$cnt_seq 				 = 0;
					$cnt_seq_single 	     = 0;
					
					if( !EMPTY( $or_pass_arr['pass_arr'] ) )  
					{
						foreach( $or_pass_arr['pass_arr'] as $key_res_m => $val_res_m )
						{
							
							foreach($val_res_m as $key_res => $val_res)
							{	

								if( !EMPTY( $val_res ) )
								{
									if( 
										!is_numeric($key_res)
									)
									{
										
										foreach( $val_res as $k => $v )
										{
											$useKey = $key_res_m;

											if(!empty(static::$ajd_prop['grouping_queue']))
											{
												$useKey = $cnt_seq_single;
											}


											$or_success[$useKey][ $key_res ][$k]['passed'][] 		= $orResultArr[Abstract_common::LOG_AND]['passed_field_or'][$field_key][ $key_res_m ][$key_res];

											if( !EMPTY( $v ) AND ISSET( $v[0] ) )
											{
												if(!empty(static::$ajd_prop['grouping_queue']))
												{
													if(isset($or_pass_arr['sequence_check'][$k][static::$ajd_prop['grouping_queue']][$field_key][$key_res][0]))
													{
														$or_success[$useKey][ $key_res ][$k]['sequence_check'][] = $or_pass_arr['sequence_check'][$k][static::$ajd_prop['grouping_queue']][$field_key][$key_res][0];				
													}
													else
													{
														$or_success[$useKey][ $key_res ][$k]['sequence_check'][] = null;
													}
																
												}
												else
												{
													$or_success[$useKey][ $key_res ][$k]['sequence_check'][] = null;
												}

												$or_success[$useKey][ $key_res ][ $k ]['rules'][] 		= $v[0];
												$or_success[$useKey][ $key_res ][ $k ]['satisfier'][] 	= $v[1];

												$or_success[$useKey][ $key_res ][ $k ]['key_multi'][] 		= $key_res_m;

												$or_success[$useKey][ $key_res ][ $k ]['cus_err'][] 		= $v[2][0][ $v[0] ][ $v[5]['rule_key'] ];
												$or_success[$useKey][ $key_res ][ $k ]['values'][] 		= $v['values'][$v[0]];

												$or_success[$useKey][ $key_res ][ $k ]['append_error'][] = $v[3][0][$v[0]][ $v[5]['rule_key'] ];
												
												$or_success[$useKey][ $key_res ][ $k ]['rule_key'][] = $v[5]['rule_key'];

												$or_success[$useKey][ $key_res ][ $k ]['rule_obj'][] 	= $v[4];

												$or_success[$useKey][ $key_res ][$k]['field'][] = $field_key;

												if(!empty(static::$ajd_prop['grouping_queue']))
												{
													$cnt_seq_single++;
												}

											}

											$cnt++;
										}
										
									}
									else 
									{
										if( ISSET( $val_res[0] ) )
										{
											$useKey = $key_res;

											if(!empty(static::$ajd_prop['grouping_queue']))
											{
												$useKey = $cnt_seq_single;
											}

											$or_success[$key_res_m][ $useKey ]['passed'][] 		= $orResultArr[Abstract_common::LOG_AND]['passed_or'][$key_res_m][$key_res];

											if(!empty(static::$ajd_prop['grouping_queue']))
											{
												$or_success[$key_res_m][ $useKey ]['sequence_check'][] = $or_pass_arr['sequence_check'][static::$ajd_prop['grouping_queue']][$field_key][$key_res_m][$key_res];						
											}
											else
											{
												$or_success[$key_res_m][ $useKey ]['sequence_check'][] = null;
											}

											$or_success[$key_res_m][ $useKey ]['field'][] = $field_key;

											$or_success[$val_res[0]][ $useKey ]['rules'][] 			= $val_res[0];
											$or_success[$val_res[0]][ $useKey ]['satisfier'][] 		= $val_res[1];
											$or_success[$val_res[0]][ $useKey ]['cus_err'][] 		= $val_res[2][0][ $val_res[0] ][ $val_res[5]['rule_key'] ];
											$or_success[$val_res[0]][ $useKey ]['values'][] 		= $val_res['values'][$val_res[0]];

											$or_success[$val_res[0]][ $useKey ]['rule_obj'][] 		= $val_res[4];

											$or_success[$val_res[0]][ $useKey ]['rule_key'][] 		= $val_res[5]['rule_key'];

											
											$or_success[$val_res[0]][ $useKey ]['append_error'][$val_res[0]][ $val_res[5]['rule_key'] ] 	= $val_res[3][0][ $val_res[0] ][ $val_res[5]['rule_key'] ];

											if(!empty(static::$ajd_prop['grouping_queue']))
											{
												$cnt_seq_single++;
											}

										}
										


										$cnt++;
									}
								}
								
							}

						}
					}


					if(
						isset($or_passed_per['auto_arr_result'])
						&& 
						!empty($or_passed_per['auto_arr_result'])
					)
					{
						if(!empty($or_passed_per['passed_per']))
						{
							foreach($or_passed_per['passed_per'] as $valuesKey => $valuesDetails)
							{
								foreach($valuesDetails as $rulesKey => $resultDetails)
								{
									
									$mainDetails = $resultDetails['details'];
									$or_passed_per_pass[$fk]['field_name'] = $mainDetails['field'];

									$or_passed_per_pass[$fk]['details'][$valuesKey][$rulesKey] = [
										'rule' => $resultDetails['rule_name'],
										'details' => $mainDetails
									];
									

								}
							}
						}
						

						// print_r($or_passed_per);
					}
					else
					{
						if(!empty($or_passed_per['passed_per']))
						{
							foreach($or_passed_per['passed_per'] as $rulesKey => $resultDetails)

							{
								
								$mainDetails = $resultDetails['details'];

								$or_passed_per_pass[$fk]['field_name'] = $mainDetails['field'];

								$or_passed_per_pass[$fk]['field_name'] = $mainDetails['field'];

								$or_passed_per_pass[$fk]['details'][0][$rulesKey] = [
									'rule' => $resultDetails['rule_name'],
									'details' => $mainDetails
								];
								
							}
						}
					}

					$fk++;
				}

				$or_field_arr 					= array();

				if( !EMPTY( static::$useContraintGroup ) )
				{
					$or_field_name 				= current( array_keys( static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_OR ] ) );

					$or_field_arr 				= static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_OR ];
 			 	}
 			 	else
 			 	{
					$or_field_name 				= current( array_keys( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ] ) );
					$or_field_arr 				= static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_OR ];
 			 	}

				$details 					= $or_field[ $or_field_name ][ Abstract_common::LOG_AND ];
				$field_arr 					= $this->format_field_name( $or_field_name );
				
				$subCheck 					= $this->_processOrCollection( $or_field_arr, $or_success, $or_field, $data, $or_passed_per_pass );

				$value_or = (isset($data[$field_arr['orig']])) ? $data[$field_arr['orig']] : null;

				if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']] ) )
				{
					$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']];

					unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_arr['orig']]);


					$this->_runEvents($eventLoad, $value_or, $field_arr['orig']);
				}

				if(!in_array(0, $subCheck))
				{
					if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']] ) )
					{
						$eventFails 	= static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']];

						unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_arr['orig']]);
						$this->_runEvents($eventFails, $value_or, $field_arr['orig']);
					}
				}
				else
				{
					if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']] ) )
					{
						$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']];

						unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_arr['orig']]);
						$this->_runEvents($eventSuccess, $value_or, $field_arr['orig']);
					}
				}

				$or_check 					= array_merge( $or_check, $subCheck );
			}

		}

		$check_and_arr 		= NULL;

		if( !EMPTY( static::$useContraintGroup ) )
		{
			$check_and_arr 	= static::$ajd_prop[static::$useContraintGroup][ 'fields' ][ Abstract_common::LOG_AND ];
		}
		else
		{
			if( ISSET( static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ] ) )
			{
				$check_and_arr 	= static::$ajd_prop[ 'fields' ][ Abstract_common::LOG_AND ];
			}
		}

		if( !EMPTY( $check_and_arr ) )
		{
			$and_field 						= $check_and_arr;

			if( !EMPTY( $and_field ) )
			{
				foreach( $and_field as $field_key => $field_value )
				{
					$realFieldKey 	= Validation_helpers::getParentPath($field_key);

					$fieldValueOr 	= array();

					if( ISSET( $field_value[Abstract_common::LOG_OR] ) )
					{
						$fieldValueOr = $field_value[Abstract_common::LOG_OR];
					}

					$propScene 		= $this->clearScenario( $field_value[Abstract_common::LOG_AND], $fieldValueOr );

					$field_value[Abstract_common::LOG_AND] 	= $propScene['prop_and'];
					$field_value[Abstract_common::LOG_OR] 	= $propScene['prop_or'];

					if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) OR ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
					{
						$and_search = array();
						$or_search 	= array();

						if( ISSET( $field_value[Abstract_common::LOG_AND]['scenarios'] ) )
						{
							$and_search = $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_AND]['scenarios'] );
						}

						if( ISSET( $field_value[Abstract_common::LOG_OR]['scenarios'] ) )
						{
							$or_search 	= $this->array_search_recursive( $field_key, $field_value[Abstract_common::LOG_OR]['scenarios'] );
						}
						
						if( !EMPTY( $and_search ) OR !EMPTY( $or_search ) )
						{
							break;
						}
					}

					if( ISSET( $data[ $realFieldKey ] ) )
					{
						$value 				= $data[ $realFieldKey ];
					}
					else 
					{
						$value 				= '';
					}

					if( $middleware )
					{

					}
					else 
					{
						if( $paramValidator->validate($field_key) )
						{
							$field_key 		= Validation_helpers::removeParentPath( $realFieldKey, $field_key );
						}

						if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key] ) )
						{
							$eventLoad 	= static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key];

							unset(static::$ajd_prop['events'][Abstract_common::EV_LOAD][$field_key]);


							$this->_runEvents($eventLoad, $value, $field_key);
						}

						if(!empty(static::$ajd_prop['groupings']))
						{
							static::$ajd_prop['cache_groupings'] = static::$ajd_prop['groupings'];
						}

						$andPromise 		= $this->checkArr( $field_key, $value, array(), TRUE, Abstract_common::LOG_AND, $field_value, null, true );

						$this->processFieldRulesSequence();

						$andPromises[] 		= $andPromise;

						$val_and_fails 		= $this->validation_fails( $field_key );

						$and_check[] 		= $val_and_fails;

						if($val_and_fails)
						{
							if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key] ) )
							{
								$eventFails 	= static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key];

								unset(static::$ajd_prop['events'][Abstract_common::EV_FAILS][$field_key]);
								$this->_runEvents($eventFails, $value, $field_key);
							}
						}
						else
						{
							if( ISSET( static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key] ) )
							{
								$eventSuccess 	= static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key];

								unset(static::$ajd_prop['events'][Abstract_common::EV_SUCCESS][$field_key]);
								$this->_runEvents($eventSuccess, $value, $field_key);
							}
						}
					}

				}
			}

		}

		$orAllPromises = null;
		$andAllPromises = null;
		$allPromises = [];
		$allPromise = null;

		if(!empty($orPromises))
		{
			$orAllPromises = PromiseHelpers::any($orPromises);
		}

		if(!empty($andPromises))
		{
			$andAllPromises = PromiseHelpers::all($andPromises);
		}

		if(!empty($orAllPromises))
		{
			$allPromises[] = $orAllPromises;
		}

		if(!empty($andAllPromises))
		{
			$allPromises[] = $andAllPromises;
		}

		if(!empty($allPromises))
		{
			$allPromise = PromiseHelpers::all($allPromises);	
		}

		$realEv = $ev;
		if(!empty($allPromise))
		{
			$realEv = $allPromise;
		}
		
		$obs->attach_observer( 'passed', $realEv, array( $this ) );
		$obs->attach_observer( 'fails', $realEv, array( $this ) );
		
		if( in_array( 1, $and_check ) OR in_array( 1, $or_check ) ) 
		{
			$obs->notify_observer( 'fails' );
		}

		if( !in_array( 1, $and_check ) AND !in_array( 1, $or_check ) ) 
		{
			$obs->notify_observer( 'passed' );
		}

		$this->reset_check_group();
		$this->reset_all_validation_prop();

		return $realEv;

	}