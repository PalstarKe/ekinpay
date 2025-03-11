<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Massactivation extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isLogin();
		isKena();
		ifClient();
		isPermitted('user_module', [11, 12, 13], true);
	}

	public function index()
	{
		isPermitted('user_all_module', [11, 12, 13], true);
		redirect('user/all');
	}

	public function massactivate()
	{
		isPermitted('user_mass_activation', [11, 12, 13], true);
		$errorMessages = [];
		$successMessages = [];
		if ((settings()[0]->kenalimit <= totalUsers()) || (settings()[0]->kenalimit == NULL)) {
			$this->session->set_flashdata('error', 'Your User Limit is Over. Please Contact Us For Upgrade.');
			redirect('support');
		}

		$newusers = 0;
		$oldusers = 0;
		$activatedusers = 0;
		$renewedusers = 0;
		$packagePrice = 0;
		$adminProfit = 0;
		$franchiseProfit = 0;
		$franchisePrice = 0;
		$dealerProfit = 0;
		$dealerPrice = 0;
		$subdealerProfit = 0;
		$subdealerPrice = 0;
		$userIDs = $this->input->post('usersids', true);
		$selectedPackage = $this->input->post('package', true);
		$currentRoleID = $this->session->user_id;
		$userIDsArr = explode(',', $userIDs);

		for ($x = 0; $x < count($userIDsArr); $x++) {
			$userID = $userIDsArr[$x];
			if (isset($userID) && !empty($userID)) {
				if ((10 < strlen($userID)) || (strpos($userID, ':') !== false)) {
					if (decryptInputPost($userID)) {
						$userID = decryptInputPost($userID);
					}
					else {
						$userID = NULL;
					}
				}
				if (isset($userID) && !empty($userID)) {
					$userData = $this->main->getUserinfoByID($userID);

					if ($userData) {
						isUserAccessible($userID);
						$userData = $userData[0];
						$discount = $userData->discount;
						$userStatus = $userData->status;
						$userName = $userData->username;
						$username = $userData->username;
						$userSalePerson = $userData->saleperson;

						if ($selectedPackage == 'current') {
							$package = $userData->package;
						}
						else {
							$package = $selectedPackage;
						}

						$fpackagePermission = $this->packagesM->profilePackagePermission($package, $userSalePerson);
						if (isset($fpackagePermission) && is_array($fpackagePermission) && $fpackagePermission[0]) {
							if (isset($userData->connectiontype) && !empty($userData->connectiontype) && (0 < $userData->connectiontype)) {
								$packageData = $this->main->getJoinPackagesByPkgID($package);

								if ($packageData) {
									$packageID = $packageData[0]->id;
									$packageGroup = $packageData[0]->groupname;
									$packagePool = $packageData[0]->pool;
									$packageExpirePool = $packageData[0]->expirepool;
									$packageQuata = $packageData[0]->dataqt;
									$packageVolume = $packageData[0]->dataqtvol;
									$pkgVolLeftOver = $packageData[0]->left_over_volumes;
									$pkgSessionQt = $packageData[0]->sessionqt;
									$pkgSessionTime = $packageData[0]->sessiontime;
									$pkgSessionLeftOver = $packageData[0]->left_over_sessions;
									$billingSystem = $packageData[0]->billing_type;
									$packageVatType = $packageData[0]->vat_type;
									$packageVat = $packageData[0]->vat;
									$packageOtherFeeType = $packageData[0]->extra_fee_type;
									$packageOtherFee = $packageData[0]->extra_fee;
									$packageAutoPayment = $packageData[0]->autopayment;
									$packageAutoRenew = $packageData[0]->autorenew;
									$packageDurationType = $this->packagesM->packageDurationTypes($packageData[0]->duration_type);
									$expirationData = $this->expirationM->expireDate($userData, $packageData);
									$newExpirationDate = $expirationData['expiration']['newExpiration'];
									$currentExpiration = $expirationData['expiration']['currentExpiration'];
									$salePersonInfo = $this->main->getAdminByAdminID($userSalePerson);

									if ($salePersonInfo) {
										$userResellerRole = $salePersonInfo[0]->role;
										$radcheckQuery = $this->main->singleQuery('radcheck', ['username' => $userData->username, 'attribute' => 'Expiration']);
										if (($userStatus == 1) && !$radcheckQuery) {
											$newusers++;
										}
										else {
											$oldusers++;
										}
										if (($userResellerRole == 1) || ($userResellerRole == 11) || ($userResellerRole == 12) || ($userResellerRole == 13)) {
											$packageAccounting = $this->accountingM->packageAccounting($userData, $packageData, NULL, $expirationData, NULL, NULL);
											if (isset($packageAccounting) && is_array($packageAccounting)) {
												if ($packageAccounting['status']) {
													$status = $packageAccounting['status'];
													$packagePrice = $packageAccounting['packagePrice'];
													$extraServiceFee = $packageAccounting['extraServiceFee'];
													$packageOtherFee = $packageAccounting['packageOtherFee'];
													$packageVat = $packageAccounting['packageVat'];
													$discount = $packageAccounting['discount'];
													$packageTotal = $packageAccounting['packageTotal'];
													$franchisePrice = $packageAccounting['franchisePrice'];
													$dealerPrice = $packageAccounting['dealerPrice'];
													$subdealerPrice = $packageAccounting['subdealerPrice'];
													$adminProfit = $packageAccounting['adminProfit'];
													$franchiseProfit = $packageAccounting['franchiseProfit'];
													$dealerProfit = $packageAccounting['dealerProfit'];
													$subdealerProfit = $packageAccounting['subdealerProfit'];
													$salePersonAd = $packageAccounting['salePersonAd'];
													$salePersonFr = $packageAccounting['salePersonFr'];
													$salePersonDr = $packageAccounting['salePersonDr'];
													$salePersonLedBal = $this->main->getLGBLByAdminID($userSalePerson);
													$userLedBal = $this->main->getLGBLByUserID($userID);

													if ($billingSystem == 1) {
														if ($salePersonLedBal < $packageTotal) {
															if ($userResellerRole == 1) {
																$sufficient = 1;
															}
															else {
																$sufficient = 0;
															}
														}
														else {
															$sufficient = 1;
														}
													}
													else if ($packageAutoPayment == 0) {
														if ($userLedBal < $packageTotal) {
															$sufficient = 0;
														}
														else {
															$sufficient = 1;
														}
													}
													else if ($salePersonLedBal < $packageTotal) {
														if ($userResellerRole == 1) {
															$sufficient = 1;
														}
														else {
															$sufficient = 0;
														}
													}
													else {
														$sufficient = 1;
													}

													$checkFrequentResponse = $this->activationM->checkActivationFrequency($userData, 'response');
													if (!isset($checkFrequentResponse) || !$checkFrequentResponse) {
														if ($sufficient == 1) {
															$activationResponse = $this->activationM->activation($userData, $packageData, $expirationData, NULL, 2);
															$activationResSuccessMsg = $activationResponse['successMessages'];
															$activationResErrorMsg = $activationResponse['errorMessages'];
															if (isset($activationResErrorMsg) && is_array($activationResErrorMsg)) {
																for ($y = 0; $y < count($activationResErrorMsg); $y++) {
																	array_push($errorMessages, $activationResErrorMsg[$y]);
																}
															}
															if (isset($activationResSuccessMsg) && is_array($activationResSuccessMsg)) {
																for ($z = 0; $z < count($activationResSuccessMsg); $z++) {
																	array_push($successMessages, $activationResSuccessMsg[$z]);
																}
															}

															if ($sufficient == 1) {
																$this->accountingM->autoPayment($userData, $packageData, $packageAccounting, $userLedBal, $salePersonLedBal, $userResellerRole);
																$salePersonLedBal = $this->main->getLGBLByAdminID($userSalePerson);
																$userLedBal = $this->main->getLGBLByUserID($userID);
																$ledgerTrxID = $this->main->insertLedger($userID, -1 * $packageTotal, 5, 1, $userSalePerson, '', 1, true);
																$resellerLedgerRes = $this->accountingM->resellerLedgerCalculation($userData, $packageData, $packageAccounting, $userLedBal, $salePersonLedBal, $userResellerRole);
																$reseller_paid_amount = 0;
																if (isset($resellerLedgerRes['userRestBalance']) && (0 < $resellerLedgerRes['userRestBalance'])) {
																	$reseller_paid_amount = $resellerLedgerRes['userRestBalance'];
																}
																else {
																	$reseller_paid_amount = 0;
																}

																$this->main->insertInvoice(['id' => $userID, 'ispid' => $userData->ispid, 'balance' => $userLedBal], ['id' => $package], [
																	'id'   => $userSalePerson,
																	'role' => $userResellerRole,
																	0      => ['packagePrice' => $packagePrice, 'adminProfit' => $adminProfit],
																	1      => ['franchisePrice' => $franchisePrice, 'franchiseProfit' => $franchiseProfit],
																	2      => ['franchisePrice' => $franchisePrice, 'franchiseProfit' => $franchiseProfit, 'dealerPrice' => $dealerPrice, 'dealerProfit' => $dealerProfit],
																	3      => ['franchisePrice' => $franchisePrice, 'franchiseProfit' => $franchiseProfit, 'dealerPrice' => $dealerPrice, 'dealerProfit' => $dealerProfit, 'subdealerPrice' => $subdealerPrice, 'subdealerProfit' => $subdealerProfit]
																], ['date' => $newExpirationDate, 'lastExpiration' => $currentExpiration, 'newExpiration' => $newExpirationDate, 'note' => '', 'header_note' => '', 'footer_note' => '', 'ledger_trx_id' => $ledgerTrxID], ['discount' => $discount, 'vat' => $packageOtherFee + $packageVat, 'amount' => $packageTotal, 'reseller_paid_amount' => $reseller_paid_amount]);
																$usersinfoData['last_expiration_date'] = date('Y-m-d H:i:s', strtotime($currentExpiration));
																$usersinfoData['activation_by'] = $currentRoleID;
																if (isset($userData->activation_date) && !empty($userData->activation_date)) {
																	$usersinfoData['activation_date'] = date('Y-m-d H:i:s');
																}
																else {
																	$usersinfoData['renew_date'] = date('Y-m-d H:i:s');
																}

																$usersinfoData['connectionstatus'] = 1;
																$usersinfoData['status'] = 2;
																$usersinfoData['smsstatus'] = 1;
																$packageDataQuota = $this->activationM->getPackageDataQuota($packageData, $userData);
																$usersinfoData['is_enabled'] = $packageDataQuota['isVolQtEnabled'];
																$usersinfoData['qt_total'] = $packageDataQuota['volQtTotal'];
																$packageSessionQuota = $this->activationM->getPackageSessionQuota($packageData, $userData);
																$usersinfoData['qt_total_session'] = $packageSessionQuota['sessionQtTotal'];
																$usersinfoData['package'] = $packageID;
																$usersinfoData['c_package'] = $packageID;
																$usersinfoData['is_qt_expired'] = 0;
																$usersinfoData['qt_used'] = 0;
																$usersinfoData['qt_session'] = 0;
																$updateUsersinfoRes = $this->main->singleUpdate('usersinfo', $usersinfoData, ['username' => $userData->username]);
																$this->main->insertActivity('User Mass Activation', $userID);

																if (if_SMSEnable()) {
																	if (chkSMSAlert(17) && getSMSAlert(17)) {
																		if (getUserSMSStatus($userID) && getUserSalespersonSms($userID, $userData->saleperson)) {
																			$mobile = $userData->mobile;
																			$smsText = getSMSAlert(17)->template;
																			$smsText = getFinalSMSText($smsText, 2, $userID);
																			$smsRes = sendSMS($mobile, $smsText);
																			$smsData = ['smsAlert' => 17, 'destination' => $mobile, 'message' => $smsText, 'userID' => $userID, 'adminID' => 0];
																			$responseSmsDelivery = $this->smsM->insertDelivery($smsRes, $smsData);
																		}
																	}
																}
															}
														}
														else {
															array_push($errorMessages, 'Insufficient Salesperson/User Balance, Username : ' . $userName);
														}
													}
													else {
														array_push($errorMessages, 'Too Frequent User Activation, Username : ' . $userName);
													}
												}
												else {
													array_push($errorMessages, $packageAccounting['error'] . ', Username : ' . $userName);
												}
											}
											else {
												array_push($errorMessages, 'Invalid Package Accounting, Username : ' . $userName);
											}
										}
										else {
											array_push($errorMessages, 'Invalid Reseller Role, Change Reseller, Username : ' . $userName);
										}
									}
									else {
										array_push($errorMessages, 'Reseller Not Found, Update Reseller, Username : ' . $userName);
									}
								}
								else {
									array_push($errorMessages, 'Package Not Found, Username : ' . $userName);
								}
							}
							else {
								array_push($errorMessages, 'No Connection Type Found, Username : ' . $userName);
							}
						}
						else {
							array_push($errorMessages, 'Reseller Not Eligble To Use This Package, ID ' . $userID);
						}

						continue;
					}

					array_push($errorMessages, 'User Not Found, ID ' . $userID);
				}
			}
		}

		$this->session->set_flashdata('successMessages', $successMessages);
		$this->session->set_flashdata('errorMessages', $errorMessages);
		$msg = 'Activated ' . $activatedusers . '/' . $newusers . ', Renewed ' . $renewedusers . '/' . $oldusers;
		if ((0 < $activatedusers) || (0 < $renewedusers)) {
			$this->session->set_flashdata('success', $msg);
		}
		else {
			$this->session->set_flashdata('errro', $msg);
		}

		redirect('user/all');
	}
}

?>