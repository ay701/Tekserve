<html>
<head>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.15/angular-sanitize.min.js"></script>
<script src="js/ng-csv.js"></script>
</head>
<body>

<style>
table.table-border, td.table-border {
    border: 2px solid black;
}

tr.table-border {
	font-weight:bold;
}

.redFont {
	color: #B92828;
}

.greenFont {
	color: #6CC417;
}

.SROloadingBar{
	margin-top: 400px;
	font-size: 42px;
	width: 100%;
	font-weight: bold;
	text-align: center;
/*	background: #EEEEEE;
	border: 1px solid #DDDDDD;
	border-radius: 5px;*/
}

.SROloadingBarAnno{
	font-size: 24px;
	color: #999999;
	font-weight: normal;
	padding-top: 15px
}
</style>

<div ng-app="myApp" ng-controller="myCtrl">

	<div class="row navbar navbar-inverse navbar-fixed-top" style="background: #ADD8E6; padding: 20px 15px 20px 15px; box-shadow: 5px 5px 5px #888888;">
		<div class="col-md-1">
			<a href="/">
			<?php 
			$my_report_id = -1;
			require_once("incl-auth.php");

			$me = $_SERVER['SCRIPT_URL']; // URL without query string
			if($me == "/" or $me == "/index.php"){
				echo '<img src="/images/logos/shopkeeper-full.png" alt="Tekserve Shopkeeper" width="254" border="0" />' ;
			}else{ 
				echo '<img src="/images/logos/shopkeeper.png" alt="Tekserve Shopkeeper" width="200" border="0" />';
			} ?>
			</a>
		</div>
		<div class="col-md-10" style="font-size:22px; font-weight:bold;" align="center">
			New Apple Account Report &nbsp;
			<select ng-model="selectedCompany" 
	            	ng-change="getAppleData()" 
	            	style="-webkit-appearance: menulist-button;" > 
	            <option ng-repeat="company in companies" ng-selected="{{company==selectedCompany}}" value="{{company}}">{{company}}</option>
			</select> 
			<select ng-model="selectedFilterOption" 
	            	ng-change="getAppleData()" 
	            	style="-webkit-appearance: menulist-button;" > 
	            <option ng-repeat="filterOption in filterOptions" ng-selected="{{filterOption==selectedFilterOption}}" value="{{filterOption}}">{{filterOption}}</option>
			</select>
		</div>
		<div class="col-md-1" align="right">
			<?php
			if(@$auth_who == "Anonymous" or @$auth_who == "") {
				// Save the current location in a hashed string
				$here = $_SERVER['REQUEST_URI'];
				$here = urlencode(base64_encode($here));
				echo '<span>Not authenticated ';
				if(strpos($_SERVER['PHP_SELF'], "signout.php") === false){
					echo '(<a href="https://'.$_SERVER['HTTP_HOST'].'/signin.php?there='.$here.'">Sign in</a>)';
				}
				echo '</span>';
			}else{
				echo '<span>Signed in as <b>'.ucwords($auth_who).'</b> (<a style="cursor:pointer;" onclick="logout();">sign out</a>)</span>';
			}
			
			if($my_report_id > 0 or $my_report_id < -1) {
				echo "\t".'<span>';
				echo '<a href="/?group='.str_replace(" ","_",$auth_report_group).'">'.$auth_report_group."</a> &gt; ";
				echo '<a href="'.$auth_report_url.'">'.$auth_report_name.'</a>';
				echo "</span>\n";
			}
			?>
		</div>
	</div>

	<div style="padding: 0px 15px 0px 15px; margin-top:140px">

		<div loading-indicator></div>

		<div id='dataBody'>

	    <div class="row" ng-show="grand!=null">
		    <div class="col-md-6">
		    	<table class="table table-bordered" style="width: 100%; border: 2px solid #333333">
					<tr style="background: #333333; color:#FFFFFF; font-weight:bold">
						<td>Total Results</td>
						<!-- <td></td> -->
						<td>Hardware</td>
						<td>iPad</td>
						<td>AppleCare</td>
						<td>Total Apple</td>
					</tr>
					<tr style="font-weight:bold">
						<td>{{customers.length}}</td>
						<!-- <td>Grand Totals</td> -->
						<td>{{grand.grand_hardware}}</td>
						<td>{{grand.grand_iPad}}</td>
						<td>{{grand.grand_appleCare}}</td>
						<td>{{grand.grand_apple}}</td>
					</tr>
				</table>
		    </div>
		    <div class="col-md-6" ng-show="selectedFilterOption=='Qualified'">
		    	<button class="btn btn-primary btn-lg" filename="{{ downloadfilename }}.csv" ng-csv="csv_customers" csv-header="getHeader()">Export</button>
		    </div>
		</div>

	    <div class="row" ng-repeat="customer in customers | orderBy:'Name'">
			<div class="col-md-12">
				<div>
					<table class="table" style="margin-bottom:35px;">
						<tr>
							<td style="padding:0px">
							
								<table class="table table-border" style="margin-bottom:-5px; background: #EEEEEE">
									<tr>
										<td width="30%">

											<table class="table" style="background: #EEEEEE; border:0px; width:">
												<tr>
													<td>

														<table>
															<tr>
																<td rowspan="3" style="padding-top:0px; vertical-align: top;">
																	<img src="http://shopkeeper.tekserve.com/images/{{customer.icon}}">&nbsp;
																</td>
																<td style="font-weight:bold; font-size:28px; font-family:; padding-left:10px">
																	<div ng-if="customer.cust_id">
																		<a target="_blank" href="http://shopkeeper.tekserve.com/customer/edit.php?cust_id={{customer.cust_id}}&scope=Anywhere">
																			{{customer.Name}}
																		</a>
																	</div>
																	<div ng-if="!customer.cust_id">
																		{{customer.Name}}
																	</div>
																</td>
															</tr>
															<tr>
																<td style="padding:10px">
																	<div ng-if="customer.tag_string">
																		<span class="glyphicon glyphicon-tags"></span> &nbsp; {{customer.tag_string}}
																	</div>															
																</td>
														 	</tr>
															<tr>
																<td style="padding:0px 10px 0px 10px">
																	<div ng-if="customer.TekSalesRep">
																		<span class="glyphicon glyphicon-user"></span> &nbsp; TekRep: {{customer.TekSalesRep}}
																	</div>															
																</td>
														 	</tr>
														 </table>
												</tr>
											</table>
										
										</td>
										<td width="70%">
										
											<table class="table table-border">
												<tr class="table-border">
													<td class="table-border">Previous 4 Quarters Apple Spend</td>
													<td class="table-border">Current Quarter Apple Spend</td>
													<td class="table-border">Current Quarter Total Spend</td>
													<td class="table-border">% Apple Spend</td>
												</tr>
												<tr>
													<td class="table-border">{{customer.previous4QuarterAppleSpend}} / $2,500</td>
													<td class="table-border"><span class="{{customer.colorIndicator}}">{{customer.currentQuarterAppleSpend}}</span> / $5,000</td>
													<td class="table-border">{{customer.currentQuarterTotalSpend}}</td>
													<td class="table-border">{{customer.currentQuarterPercentAppleSpend}}%</td>
												</tr>
											</table>

										</td>								
									</tr>

								</table>
							
							</td>
						</tr>
						<tr>
							<td style="padding:0px">

								<table class="table table-border" ng-show="customer.sros.length>0" style="margin-bottom:0px; margin-top:0px">
										<tr class="table-border">
											<td class="table-border">TekCompany</td>
											<td class="table-border">Date</td>
											<td class="table-border">Invoice#</td>
											<td class="table-border">Intake_by</td>
											<td class="table-border">Contact</td>
											<td class="table-border">Phone#</td>
											<td class="table-border">Email</td>
											<td class="table-border">Hardware</td>
											<td class="table-border">iPad</td>
											<td class="table-border">AppleCare</td>
											<td class="table-border">Total Apple</td>
											<td class="table-border">Other</td>
											<td class="table-border">% Apple</td>
										</tr>
										<tr ng-repeat="sro in customer.sros">
											<td class="table-border">{{sro.tekCompany}}</td>
											<td class="table-border">{{sro.completedDate}}</td>
											<td class="table-border">
												<a ng-href="tek://find?sro={{sro.sronumber}}">{{sro.sronumber}}</a>
											</td>
											<td class="table-border">{{sro.intakeBy}}</td>
											<td class="table-border">{{sro.Contact}}</td>
											<td class="table-border">{{sro.phone}}</td>
											<td class="table-border">{{sro.Email}}</td>
											<td class="table-border">{{sro.Hardware}}</td>
											<td class="table-border">{{sro.iPad}}</td>
											<td class="table-border">{{sro.AppleCare}}</td>
											<td class="table-border">{{sro.AppleTotal}}</td>
											<td class="table-border">{{sro.other}}</td>
											<td class="table-border">{{sro.ApplePercentSpend}}</td>
										</tr>
										<tr class="table-border">
											<td class="table-border"></td>
											<td class="table-border"></td>
											<td class="table-border"></td>
											<td class="table-border"></td>
											<td class="table-border"></td>
											<td class="table-border"></td>
											<td class="table-border"></td>
											<td class="table-border">{{customer.currentQuarterHardwareSpend}}</td>
											<td class="table-border">{{customer.currentQuarteriPadSpend}}</td>
											<td class="table-border">{{customer.currentQuarterAppleCareSpend}}</td>
											<td class="table-border">{{customer.currentQuarterAppleSpend}}</td>
											<td class="table-border">{{customer.currentQuarterOtherSpend}}</td>
											<td class="table-border"></td>
										</tr>
									</tbody>
								</table>

							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		</div>

	</div>

</div> <!--End of Controller-->

<script>
var app = angular.module('myApp', ["ngSanitize", "ngCsv"]);

app.config( [
    '$compileProvider',
    function( $compileProvider )
    {   
        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|tek|ftp|mailto|chrome-extension):/);
        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
    }
]);

app.factory("customers", function($http, $q){
 
    var getCustomerData = function(selectedCompany, selectedFilterOption){
        var canceller = $q.defer();
 
        var cancel = function(reason){
            canceller.resolve(reason);
        };
 
        var promise =
            $http.get("ajax_Apple_Quarter.php?company="+selectedCompany+"&filterOption="+selectedFilterOption, { timeout: canceller.promise })
            	.then(function(response){
            		console.log(response.data);
                	return response.data;
                });
 
        return {
            promise: promise,
            cancel: cancel
        };
    };
 
    return {
        getCustomerData: getCustomerData
    };
 
});

app.controller('myCtrl', function($scope, $http, customers) {

	$scope.requests = [];
	$scope.customers = [];
	$scope.csv_customers = [];

	$scope.companies = ["Tekserve","T2", "All"];
	$scope.selectedCompany = "Tekserve";
	$scope.filterOptions = ["Qualified","<= $2,500 to qualify","<= $1,000 to qualify","BIZ CYT","BIZ TR","BIZ DIL","BIZ WTB","BIZ JCS","BIZ MDA","BIZ DRL","Show All"];
	$scope.selectedFilterOption = "Qualified";
	$scope.downloadfilename = "AppleReport_"+new Date().toISOString().slice(0,10);
	$scope.getHeader = function () {
		return ["Customer Name", "ExportPN", "Qty", "$Value of Client Invoice", "Contact", "Invoice#", "TekCompany", "TekSalesRep", "IntakeBy", "CompletedDate"]
	};
    
    $scope.getAppleData = function() {

    	// If there is ongoing request, cancel first
    	if($scope.requests.length>0)
    		$scope.cancel($scope.requests.pop());

    	var request = customers.getCustomerData($scope.selectedCompany,$scope.selectedFilterOption);
    	$scope.requests.push(request);

		request.promise.then(function(Results){

			$scope.grand = Results.pop();
			$scope.customers = Results;

			var tmp_arr;

			for(i=0; i<Results.length; i++){
				// console.log("xxxxxxxx");
				for(j=0;j<Results[i]['sros'].length;j++){
					for(k=0; k<Results[i]['sros'][j]['lineitems'].length; k++){

						tmp_arr = [];
						tmp_arr.push($scope.customers[i]['Name']);
						tmp_arr.push(Results[i]['sros'][j]['lineitems'][k]['ExportPN']);
						tmp_arr.push(Results[i]['sros'][j]['lineitems'][k]['quan']);
						tmp_arr.push(Results[i]['sros'][j]['AppleTotal']);
						tmp_arr.push(Results[i]['sros'][j]['Contact']);
						tmp_arr.push(Results[i]['sros'][j]['sronumber']);
						tmp_arr.push(Results[i]['sros'][j]['tekCompany']);
						tmp_arr.push($scope.customers[i]['TekSalesRep']);
						tmp_arr.push(Results[i]['sros'][j]['intakeBy']);
						tmp_arr.push(Results[i]['sros'][j]['completedDate']);
						$scope.csv_customers.push(tmp_arr);

					}
				}
			}

			// console.log($scope.csv_customers);

			clearRequest(request);

		}, function(reason){
			console.log(reason);
		});

    };

	$scope.cancel = function(request){
		request.cancel("User cancelled");
		clearRequest(request);
	};

	var clearRequest = function(request){
		$scope.requests.splice($scope.requests.indexOf(request), 1);
	};

    $scope.getAppleData();

});

app.config(function($httpProvider) {
    $httpProvider.interceptors.push(function($q, $rootScope) {
        return {
            'request': function(config) {
                $rootScope.$broadcast('loading-started');
                return config || $q.when(config);
            },
            'response': function(response) {
                $rootScope.$broadcast('loading-complete');
                return response || $q.when(response);
            }
        };
    });
});

app.directive("loadingIndicator", function() {
    return {
        restrict : "A",
        template: "<div class='SROloadingBar'><img src='http://shopkeeper.tekserve.com/images/loading.gif'>&nbsp; Preparing data ...<div class='SROloadingBarAnno'>This may take a while</div></div>",
        link : function(scope, element, attrs) {
            scope.$on("loading-started", function(e) {
            	document.getElementById('dataBody').style.display = 'none';
                element.css({"display" : ""});
            });
            scope.$on("loading-complete", function(e) {
            	document.getElementById('dataBody').style.display = '';
                element.css({"display" : "none"});
            });
        }
    };
});


</script>

</body>
</html>
