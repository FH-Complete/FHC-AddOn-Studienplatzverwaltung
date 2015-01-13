
'use strict';

// Templates für Grid
//
//var uvCellTemplate = '<div class="ngCellText right"  ng-class="{redright: row.getProperty(col.field) < row.getProperty(\'npzBdTolUnten()\'), editeven: (!row.selected && row.rowIndex % 2 == 0), editodd: (!row.selected && row.rowIndex % 2 != 0)}" ><span class="ngCellText">{{row.getProperty(col.field)}}</span></div>';
var uvCellTemplate = '<div class="ngCellText right"  ng-class="{redright1: aggStgFc(rows(),row,col) < row.getProperty(\'npzBdTolUnten()\'), editeven: (!row.selected && row.rowIndex % 2 == 0), editodd: (!row.selected && row.rowIndex % 2 != 0)}" ><span class="ngCellText">{{row.getProperty(col.field)}}</span></div>';
var tfpUvCellTemplate = '<div class="ngCellText right"  ><span class="ngCellText">{{tfpStgUv(rows(),row,col)}}</span></div>';
var sumUvCellTemplate = '<div class="ngCellText right"  ><span class="ngCellText">{{sumStgUv(rows(),row,col)}}</span></div>';
var aggCellTemplate = '<div class="ngCellText right" ng-class="col.colIndex()"><span class="ngCellText">{{aggFc(row,col)}}</span></div>';
var cellTemplateUnsafeHTML = '<div class="ngCellText" ng-class="col.colIndex()"><span ng-cell-text ng-bind-html="row.getProperty(col.field).toString()"></span></div>';
var cellTemplateBewerberLinkHTML = '<div class="ngCellText" ng-class="col.colIndex()"><span ng-cell-text ><a ng-href="{{\'https://vilesci.technikum-wien.at/content/statistik/bewerberstatistik.php?showdetails=true&studiengang_kz=\' + row.getProperty(\'stgKz\') + \'&stsem=\' + row.getProperty(\'studiensemester\')}}" target="_blank">{{row.getProperty(col.field)}}</a></span></div>';
var cellTemplateDropOutLinkHTML = '<div class="ngCellText" ng-class="col.colIndex()"><span ng-cell-text ><a ng-href="{{\'https://vilesci.technikum-wien.at/addons/datamining/vilesci/chart.php?chart_id=1&varname0=stsem&var0=\' + row.getProperty(\'studiensemester\')}}" target="_blank">{{row.getProperty(col.field)}}</a></span></div>';
var aggFooterCellTemplate = '<div class="ngCellText right" ng-class="col.colIndex()"><span class="ngCellText">{{aggFooterFc(rows(), col)}}</span></div>';

angular.module('app.controllers', ['ui.bootstrap','ui.bootstrap.dropdownToggle', 'ngGrid', 'ngSanitize']).

controller("UvToolCtrl", ['$scope', '$window', '$modal', '$log', '$timeout', '$location','$anchorScroll','uvService', 'UtilityService',
    function($scope, $window, $modal, $log, $timeout, $location, $anchorScroll,uvService, utilityService) {

		
		$scope.busy = false;
        
        $scope.semesterSortFn = function(a,b)
        {
            var aJahr = parseInt(a.substr(2));
            var bJahr = parseInt(b.substr(2));
            var aSem = a.substr(0,2);
            var bSem = b.substr(0,2);
            if (aJahr !== bJahr) {
                return aJahr-bJahr;
            } else if (aSem === 'SS' && bSem === 'WS') {
                return -1;
            } else if (aSem === 'WS' && bSem === 'SS') {
                return 1;
            }
            return 0;
        };


        $scope.cellEditableConditionFc=function() {
        	if ($scope.selected !== undefined) {
				if ($scope.selected.daten.status === 'eingereicht') {
					return false;
				}        		
        	} 
        	return true;
        	
        };

        $scope.$on('ngGridEventEndCellEdit', function (event) {
            $scope.rowData = event.targetScope.row.entity;
            $log.debug('GridEventEndCellEdit'); 
        });

        $scope.aggFooterFc=function(rows, col){
           
            var subsum = 0;
				var val;
				var num = null;
				angular.forEach(rows, function(a) {
					val = a.getProperty(col.field); //a.entity[col.field]; //a.getProperty('fields.timetracking.remainingEstimateSeconds');
					num = parseFloat(val);
					if (num !== null && !isNaN(num)) { 
						subsum += num; 
					} else {
						//console.log("NaN");
					}
				});
            return subsum;
        };

		$scope.aggFc=function(rowAgg,col){
			var sum = 0;

			var calculateChildren = function(cur) {
				var subsum = 0;
				var val;
				var num = null;
				angular.forEach(cur.children, function(a) {
					val = a.getProperty(col.field); //a.entity[col.field]; //a.getProperty('fields.timetracking.remainingEstimateSeconds');
					num = parseFloat(val);
					if (num !== null && !isNaN(num)) { 
						subsum += num; 
					} else {
						//console.log("NaN");
					}
				});
				return subsum;
			};
			
			var calculateAggChildren = function(cur) {
				var res = 0;
				res += calculateChildren(cur);
				angular.forEach(cur.aggChildren, function(a) {
				  res += calculateAggChildren(a);
				});
				return res;
			};
			
			 return calculateAggChildren(rowAgg);
			/*
			var row=rowAgg.children[0];
			if(row.entity[col.field] && col.cellFilter){
				console.log("'"+row.entity[col.field]+"'  |" +col.cellFilter);
				return $scope.$eval("'"+row.entity[col.field]+"'  |" +col.cellFilter);
			}
			return row.entity[col.field];*/
		};
		
		$scope.aggStgFc = function(rows,row,col) {
			var subsum = 0;
			var val;
			var num = null;
			var stgKz = row.getProperty('stgKz');
			var stgArt = row.getProperty('stgArt');
			var studiensemester = row.getProperty('studiensemester');
			angular.forEach(rows, function(a) {			
				if (a.getProperty('stgKz') == stgKz &&
					a.getProperty('stgArt') == stgArt &&
					a.getProperty('studiensemester') == studiensemester)
				{
					val = a.getProperty(col.field); //a.entity[col.field]; //a.getProperty('fields.timetracking.remainingEstimateSeconds');
					num = parseFloat(val);
					if (num !== null && !isNaN(num)) { 
						subsum += num; 
					} else {
						//console.log("NaN");
					}
				}
			});
            return subsum;
		}

		$scope.sumStgUv = function(rows,row,col) {
			var subsum = 0;
			var val;
			var num = null;
			var stgKz = row.getProperty('stgKz');
			var stgArt = row.getProperty('stgArt');
			var studiensemester = row.getProperty('studiensemester');
			angular.forEach(rows, function(a) {			
				if (a.getProperty('stgKz') == stgKz &&
					a.getProperty('stgArt') == stgArt &&
					a.getProperty('studiensemester') == studiensemester)
				{
					val = a.getProperty('npzUv'); 
					num = parseFloat(val);
					if (num !== null && !isNaN(num)) { 
						subsum += num; 
					} else {
						//console.log("NaN");
					}
				}
			});
			return subsum - $scope.calcTFP(subsum);
		}


		// TFP für UV berechnen
		$scope.tfpStgUv = function(rows,row,col) {
			var subsum = 0;
			var val;
			var num = null;
			var stgKz = row.getProperty('stgKz');
			var stgArt = row.getProperty('stgArt');
			var studiensemester = row.getProperty('studiensemester');
			angular.forEach(rows, function(a) {			
				if (a.getProperty('stgKz') == stgKz &&
					a.getProperty('stgArt') == stgArt &&
					a.getProperty('studiensemester') == studiensemester)
				{
					val = a.getProperty('npzUv'); 
					num = parseFloat(val);
					if (num !== null && !isNaN(num)) { 
						subsum += num; 
					} else {
						//console.log("NaN");
					}
				}
			});
			
			return $scope.calcTFP(subsum);
		}


		// Formel für TFP lt. Folien
		$scope.calcTFP = function(subsum) {
			if (subsum <= 100) {
				return (subsum * 0.1).toFixed(0);
            } else if (subsum <= 500) {
            	return (10.0 + (subsum - 100) * 0.08).toFixed(0);
            }
            return (42.0 + (subsum - 500) * 0.05).toFixed(0);
		}


        
        // Liste der gespeicherten Einstellungen
		$scope.setupOptions = [{'label': ''}];
        // Liste asynchron init
        uvService.getSetupList().then(
            function(response) {
                if (response.data.result == 1) {
                    $scope.setupOptions = response.data.setupList;
                } else {
                    $log.warn("Konnte Liste mit Einstellungen nicht laden");
                }
            }, 
            function() {
                $log.warn("Konnte Liste mit Einstellungen nicht laden");
            }
        );
	
		$scope.mergeSetupOptions = function(newList) {
			
			for(var i=0; i < newList.length; i++){
				var found = false;
				for(var j=0; j < $scope.setupOptions.length; j++){
					if ($scope.setupOptions[j].label === newList[i].label) {
						found = true;
						break;
					}
				}
				if(!found){
					$scope.setupOptions.push(newList[i]);
				}
			}
		};

		$scope.gridColumnDefs = 
			[
				{ field: 'stgKz', displayName: 'StgKz', enableCellEdit: false, width: 80, pinnable: true, groupable: true},
                { field: 'stgBezeichnung', displayName: 'Stg', enableCellEdit: false, width:80, pinnable: true, groupable: true },
                { field: 'stgArt', displayName: 'Art', enableCellEdit: false, width:80, pinnable: true, groupable: true },
                { field: 'orgForm', displayName: 'OrgForm', enableCellEdit: false, pinnable: true, width:80, groupable: true},
                { field: 'studienjahr', displayName: 'SJahr', enableCellEdit: false, pinnable: true, width:80, groupable: true, visible: true},
				{ field: 'studiensemester', displayName: 'Semester', enableCellEdit: false, sortFn:$scope.semesterSortFn,pinnable: true, width:80, groupable: true, visible: true},
				{ field: 'beginn', displayName: 'Beginn', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false},
				{ field: 'ende', displayName: 'Ende', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false},
				{ field: 'aufnahme', displayName: 'Aufnahme', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false},
				{ field: 'regelstudiendauer', displayName: 'RStd', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false},
				{ field: 'bewerber', displayName: 'Bewerber', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false,cellClass:'right', cellTemplate: cellTemplateBewerberLinkHTML, aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				{ field: 'dropout', displayName: 'Dropout', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false,cellClass:'right',  cellTemplate: cellTemplateBewerberLinkHTML,aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
                { field: 'interessenten', displayName: 'Interessenten', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false,cellClass:'right',  cellTemplate: cellTemplateBewerberLinkHTML,aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				{ field: 'studierende', displayName: 'Studierende', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false,cellClass:'right',  cellTemplate: cellTemplateBewerberLinkHTML,aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				{ field: 'foebisAktive', displayName: 'FÖBisA', enableCellEdit: false, width:80,pinnable: false, groupable: false, visible: false,cellClass:'right',  aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				{ field: 'gpzBd', displayName: 'GPZ-BD', enableCellEdit: false,width:80,pinnable: false, groupable: false,cellClass:'right', aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				//{ field: 'gpzUv', displayName: 'GPZ-UV', width:80,pinnable: false, groupable: false,cellTemplate: '<div class="ngCellText" ng-class="{redright: row.getProperty(col.field) > 30}"><div class="ngCellText right">{{row.getProperty(col.field)}}</div></div>'},
                { field: 'gpzUv', displayName: 'GPZ-UV', enableCellEdit: true,width:80,pinnable: false, groupable: false,cellTemplate:uvCellTemplate, aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate },
				{ field: 'gpzDiff()', displayName: 'GPZ-Diff', enableCellEdit: false,width:80,pinnable: false, groupable: false,cellClass:'right', aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				{ field: 'npzBd', displayName: 'NPZ-BD', enableCellEdit: false, width:80,pinnable: false, groupable: false,cellClass:'right', aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
				//{ field: 'npzUv', displayName: 'NPZ-UV', width:80,pinnable: false, groupable: false,cellTemplate:'<div class="ngCellText" ng-class="{row.rowIndex % 2 == 0 && \'even\' || \'odd\'}"><div class="ngCellText right">{{row.getProperty(col.field)}}</div></div>'},
                { field: 'npzUv', displayName: 'NPZ-UV', enableCellEdit: true,width:80,pinnable: false, groupable: false,cellTemplate:uvCellTemplate, aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
              
				{ field: 'npzDiff()', displayName: 'NPZ-Diff', enableCellEdit: false, width:80,pinnable: false, groupable: false,cellClass:'right', aggCellTemplate: aggCellTemplate, footerCellTemplate: aggFooterCellTemplate},
                    //cellTemplate:'<div class="ngCellText right" ><span class="ngCellText">{{row.getProperty(\'npzUv\') - row.getProperty(\'npzBd\')}}</div></div>'},
				{ field: 'npzBdTfp()', displayName: 'TFP BD', enableCellEdit: false, width:80,pinnable: false, groupable: false,cellClass:'right'},
				{ field: 'npzBdTolUnten()', displayName: 'Tol unten BD', enableCellEdit: false, width:130,pinnable: false, groupable: false,cellClass:'right'},
				{ field: 'tfpUv', displayName: 'TFP UV', enableCellEdit: false, width:80,pinnable: false, groupable: false,cellClass:'right',cellTemplate:tfpUvCellTemplate, visible: false},
				{ field: 'uvTolUnten', displayName: 'Tol unten UV', enableCellEdit: false, width:80,pinnable: false, groupable: false,cellClass:'right',cellTemplate:sumUvCellTemplate, visible: false}
			];


		$scope.filterOptions = {
			filterText: "",
			useExternalFilter: false
		};

        
		
		// Helper damit in den Gruppierungszeilen die ersten 5 Spalten ausgeblendet sind
        $scope.filterFn = function(col) {
            if (col.index<6) return false;
            return !col.isAggCol ;
        };
        
		// Helper um Einrückungen der Zellen in den Gruppierungszeilen zu verhindern
        $scope.calcPullLeft = function(row) {
            return "-25"*row.depth + "px";
        };
        
		$scope.translateBM = function(str) {
			if (str == 'b'  || str == 'B') return 'Bachelor';
			if (str == 'm'  || str == 'M') return 'Master';
			return '';
		}
      
		function generateGridOptions(tab) {
			return { 
				data: 'tab.uvListe',
				
				aggregateTemplate:    
					"<div ng-click=\"row.toggleExpand()\" ng-style=\"rowStyle(row)\" class=\"ngAggregate\">" +
						"<span class=\"ngAggregateText\">{{row.label CUSTOM_FILTERS}}</span>" +
						"<div ng-style=\"{marginLeft:calcPullLeft(row)}\" ng-style=\"{ 'cursor': row.cursor }\" ng-repeat=\"col in renderedColumns|filter:filterFn\" class=\"ngCell {{col.colIndex()}} {{col.cellClass}}\">" +
						"	<div class=\"ngVerticalBar\" ng-style=\"{height: rowHeight}\" ng-class=\"{ ngVerticalBarVisible: !$last }\">&nbsp;</div>" +
						"	<div ng-cell></div>" +                 
						"</div>" +
						"<div class=\"{{row.aggClass()}}\"></div>" +
					"</div>",
				// Rowtemplate für bessere Performance (von https://github.com/angular-ui/ng-grid/issues/865)
				rowTemplate: "<div ng-style=\"{ 'cursor': row.cursor }\" ng-repeat=\"col in renderedColumns\" class=\"ngCell {{col.colIndex()}} {{col.cellClass}}\">" +
					"	<div class=\"ngVerticalBar\" ng-style=\"{height: rowHeight}\" ng-class=\"{ ngVerticalBarVisible: !$last }\">&nbsp;</div>" +
					"	<div ng-cell></div>" +              
					"</div>",
				columnDefs: $scope.gridColumnDefs, //'gridColumnDefs',
				enableCellSelection: true,
				//enableCellEdit: true, 
				cellEditableCondition: 'cellEditableConditionFc()',  // undokumentiertes Feature! (siehe https://github.com/angular-ui/ng-grid/issues/864 )
				showGroupPanel: true,
				enableColumnResize:true,
				enableColumnReordering:true,
				showColumnMenu: true,
				multiSelect: false,
				showFilter: true,
				enablePinning: false,
				groupsCollapsedByDefault: false,
				//virtualizationThreshold:50,
				//enablePaging: true,
				showFooter: true,
                //plugins: [new ngGridCsvExportPlugin()],
                selectedItems: tab.gridSelection.selectedItems,
				filterOptions: tab.filterOptions, //$scope.filterOptions,
				forceSyncScrolling: false, // high performance scrolling (noch nicht dokumentiert)
				//To be able to have selectable rows in grid.
				//enableRowSelection: false
                footerRowHeight: 35,
                footerTemplate:  //"<div ng-show=\"showFooter\" class=\"ngFooterPanel\" ng-class=\"{'ui-widget-content': jqueryUITheme, 'ui-corner-bottom': jqueryUITheme}\" ng-style=\"footerStyle()\">\r" +
                                 //   "<div class=\"ngFooterContainer\" ng-style=\"footerStyle()\">\r" +
                                    '   <div class=\"ngFooterScroller\" ng-style=\"footerScrollerStyle()\">' +
                                    '       <div ng-style="{ height: col.headerRowHeight }" ng-repeat="col in renderedColumns" ng-class="col.colIndex()" class="ngHeaderCell" ng-footer-cell></div>' +
                                    '   </div>' //+
                                //    '</div>' +
                                // '</div>' +
                                // "<div ng-show=\"showFooter\" class=\"ngFooterPanel\" ng-class=\"{'ui-widget-content': jqueryUITheme, 'ui-corner-bottom': jqueryUITheme}\" ng-style=\"footerStyle()\">\r" +
                                   /*
									"<div class=\"ngTotalSelectContainer\" >\r" +
                                    "\n" +
                                    "        <div class=\"ngFooterTotalItems\" ng-class=\"{'ngNoMultiSelect': !multiSelect}\" >\r" +
                                    "\n" +
                                    "            <span class=\"ngLabel\">{{i18n.ngTotalItemsLabel}} {{maxRows()}}</span><span ng-show=\"filterText.length > 0\" class=\"ngLabel\">({{i18n.ngShowingItemsLabel}} {{totalFilteredItemsLength()}})</span>\r" +
                                    "\n" +
                                    "        </div>\r" +
                                    "\n" +
                                    "        <div class=\"ngFooterSelectedItems\" ng-show=\"multiSelect\">\r" +
                                    "\n" +
                                    "            <span class=\"ngLabel\">{{i18n.ngSelectedItemsLabel}} {{selectedItems.length}}</span>\r" +
                                    "\n" +
                                    "        </div>\r" +
                                    "\n" +
                                    "    </div>\r" */ // +
                
                                //  '</div>'
			};
		}

/*
		$scope.altGridOptions = { 
			data: 'uvTabs[0].uvListe',
			columnDefs: $scope.gridColumnDefs, //'gridColumnDefs',
            enableCellSelection: true,
            enableCellEdit: true,
			showGroupPanel: true,
			enableColumnResize:true,
			enableColumnReordering:true,
			showColumnMenu: true,
			multiSelect: false,
			showFilter: false,
			enablePinning: false,
			groupsCollapsedByDefault: false,
            virtualizationThreshold:50
		};

        $scope.removeRow = function(row) {
            var index = $scope.myData.indexOf(row.entity);
            alert(index);
            $scope.gridOptions.selectItem(index, false);
            $scope.myData.splice(index, 1);
        };*/

		
        // Tabellen Header 
        $scope.groupHeaders = {
            useColSpanStyle: true,
            groupHeaders: [
                {startColumnName: 'bewerber', numberOfColumns: 2, titleText: '<em>WS2013</em>'},
                {startColumnName: 'gpzBd', numberOfColumns: 2, titleText: '<em>SS2014</em>'}
            ]
        };
    
        /* Spalten nach denen gruppiert wird */
        $scope.groupColumns = [];
        $scope.GroupColumnOptions = [
            {key: 'stgBezeichnung', label: "Stg"},
            {key: 'stgArt', label: "StgArt"},
            {key: 'orgForm', label: "OrgForm"}];

        // geöffnete UVs
        $scope.uvTabs = [];

        $scope.statusList = [{id:'entwurf',name:'Entwurf'},
                         {id:'konsolidierung',name:'Konsolidierung'},
                         {id:'eingereicht',name:'Eingereicht'}];

/*
        $scope.$watch('original_data', function() {
            $scope.processed_data = _.map($scope.original_data, function(character) {
                return {
                    name: character.name,
                    scores: character.scores,
                    total: $scope.getSum(character.scores)
                };
            });
        }, true);

*/
        
        $scope.filter = {
          studienjahr: ''  
        };

        $scope.updateFilter = function(tab) {
            //if (!$scope.filter.studienjahr) $scope.filterOptions.filterText = '';
            //else $scope.filterOptions.filterText = 'SJahr:' + $scope.filter.studienjahr.substr(0,4);
			if (!tab.filter.studienjahr) tab.filterOptions.filterText = '';
            else tab.filterOptions.filterText = 'SJahr:' + tab.filter.studienjahr.substr(0,4);
        };

        $scope.updateEditMode = function(tab) {
           $log.debug('updateEditMode; status=' + tab.daten.status);
           if (tab.daten.status === 'eingereicht') {
                //tab.gridOptions.enableCellEdit = false;
                //tab.gridOptions.ngGrid.buildColumns();
           } else {

           }
        }

        $scope.addTab = function(tabItem) {
            $scope.uvTabs.push(tabItem);
            tabItem.active = true;
			var tabIndex = $scope.uvTabs.indexOf(tabItem);
            
            //var uvDaten = uvService.getUv(tabItem.studienjahr, tabItem.item.nr, 0);
            //var uvDatenArray = uvService.flattenDataNG(uvDaten);
			var uvDatenArray = uvService.flattenDataNG(tabItem.daten);
            tabItem.uvListe = uvDatenArray;
			tabItem.processed_data = null;
            // Liste der Studienjahre für Filter
            tabItem.studienjahre = utilityService.extractStudienjahre(uvDatenArray);
			$scope.uvListe = uvDatenArray;
			// init infodaten
			tabItem.infoData = [];
			tabItem.filterOptions = {
				filterText: "",
				useExternalFilter: false
			};
			// aktuelle Auswahl in Tabelle
            tabItem.gridSelection = {
                selectedItems: []   
            };
            tabItem.gridOptions = generateGridOptions(tabItem); //$scope.gridOptions;			
			//tabItem.gridOptions.enableCellEdit = (tabItem.daten.status == 'eingereicht'?false:true);
            // init Studienjahrfilter
			tabItem.filter = {
				studienjahr: ''  
			};
			tabItem.chart = { 
				//dataset =  "[      70,      80,      90,      85,      75,      80,      82,     90,      93,      101,     90,        80,        83,     89,      98,        100]" 
				//labels: ['WS2009','SS2010','WS2010','SS2011','WS2011','SS2012','WS2012','SS2013','WS2013','SS2014', 'WS2014', 'SS2015', 'WS2015', 'SS2016', 'WS2016', 'SS2017'] //generateStudiensemester(tabItem.daten.studienjahr, tabItem.daten.zeitraum)
				dataset: [], 
				labels: utilityService.generateStudiensemester(tabItem.daten.studienjahr, tabItem.daten.zeitraum)
			};
			// Chart aktualisieren wenn Auswahl geändert
			$scope.$watch('uvTabs[' + tabIndex + '].gridSelection.selectedItems', function() {
				if (tabItem.gridSelection.selectedItems.length > 0) {
					$log.info('selectedItems changed Stg=' + tabItem.gridSelection.selectedItems[0].stgBezeichnung); 
					/*tabItem.chart.dataset = 
							{
								offset: 10, 
								data: utilityService.extractDaten(tabItem.uvListe,
									tabItem.gridSelection.selectedItems[0].stgKz,
									tabItem.gridSelection.selectedItems[0].stgArt,
									tabItem.gridSelection.selectedItems[0].orgForm,
									'npzBdTolUnten'),
								color: 'grey',
								label: 'NPZ-UV'
							};
					*/
					tabItem.chart.dataset = [
							{
								offset: 10, 
								data: utilityService.extractDaten(tabItem.uvListe,
									tabItem.gridSelection.selectedItems[0].stgKz,
									tabItem.gridSelection.selectedItems[0].stgArt,
									tabItem.gridSelection.selectedItems[0].orgForm,
									'npzUv'),
								color: 'grey',
								label: 'NPZ-UV'
								,style: 'Stufen'
							},
							{	
								offset: 0,
								data: tabItem.infoData[tabItem.gridSelection.selectedItems[0].stgKz][tabItem.gridSelection.selectedItems[0].orgForm]['npzBd'],
								color: 'black',
								label: 'NPZ-BD'
									,style: 'Stufen'
							},
							{
								offset: 10, 
								data: utilityService.extractDaten(tabItem.uvListe,
									tabItem.gridSelection.selectedItems[0].stgKz,
									tabItem.gridSelection.selectedItems[0].stgArt,
									tabItem.gridSelection.selectedItems[0].orgForm,
									'npzBdTolUnten'),
								color: 'red',
								label: 'TFP'
									,style: 'Stufen'
									
							},
							{	
								offset: 0,
								data: tabItem.infoData[tabItem.gridSelection.selectedItems[0].stgKz][tabItem.gridSelection.selectedItems[0].orgForm]['bewerber'],
								color: 'yellow',
								label: 'Bewerber'
									,style: 'Stufen'
							},
							{	
								offset: 0,
								data: tabItem.infoData[tabItem.gridSelection.selectedItems[0].stgKz][tabItem.gridSelection.selectedItems[0].orgForm]['abbrecher'],
								color: 'orange',
								label: 'Abbrecher'
									,style: 'Stufen'
							},
							{	
								offset: 0,
								data: tabItem.infoData[tabItem.gridSelection.selectedItems[0].stgKz][tabItem.gridSelection.selectedItems[0].orgForm]['interessenten'],
								color: 'purple',
								label: 'Interessenten'
								,style: 'Stufen'
							},
							{	
								offset: 0,
								data: tabItem.infoData[tabItem.gridSelection.selectedItems[0].stgKz][tabItem.gridSelection.selectedItems[0].orgForm]['studenten'],
								color: 'blue',
								label: 'Studenten'
									,style: 'Stufen'
							},
							{	
								offset: 0,
								data: tabItem.infoData[tabItem.gridSelection.selectedItems[0].stgKz][tabItem.gridSelection.selectedItems[0].orgForm]['foebisaktive'],
								color: 'cyan',
								label: 'FÖBisAktive'
									,style: 'Stufen'
							}
							
						]
				};  // if
            }, true);
			// Grid User-Einstellungen (sichtbare Spalten, Sortierung, Gruppierung)
			tabItem.currentSetup = ''; 
            $scope.$watch('uvTabs[' + tabIndex + '].currentSetup', function() {
                $log.info('currentSetup changed; tabIndex:' + tabIndex); 				
            }, false);
					
            $scope.selected = tabItem;
            $log.debug('addTab: item.active=' + tabItem.active + '; $scope.uvTabs[0].active=' + $scope.uvTabs[0].active);
        };
		
		$scope.refreshTab = function(tabItem, tabIndex) {
            $scope.uvTabs[tabIndex] = tabItem;
            tabItem.active = true;

			var uvDatenArray = uvService.flattenDataNG(tabItem.daten);
            tabItem.uvListe = uvDatenArray;
			tabItem.processed_data = null;
            // Liste der Studienjahre für Filter
            tabItem.studienjahre = utilityService.extractStudienjahre(uvDatenArray);
			$scope.uvListe = uvDatenArray;
			tabItem.filterOptions = {
				filterText: "",
				useExternalFilter: false
			};
			// aktuelle Auswahl in Tabelle
            tabItem.gridSelection = {
                selectedItems: []
            };
            tabItem.gridOptions = generateGridOptions(tabItem); //$scope.gridOptions;			
			// init Studienjahrfilter
			tabItem.filter = {
				studienjahr: ''  
			};
			tabItem.chart = {			
				labels: utilityService.generateStudiensemester(tabItem.daten.studienjahr, tabItem.daten.zeitraum)
			};
			// Grid User-Einstellungen (sichtbare Spalten, Sortierung, Gruppierung)
			tabItem.currentSetup = '';
			
					
            $scope.selected = tabItem;
            $log.debug('refreshTab: item.active=' + tabItem.active + '; $scope.uvTabs[0].active=' + $scope.uvTabs[0].active);
        };

        $scope.selectTab = function(index) {
            $log.info('selectTab index =' + index);
            $scope.uvTabs[index].active=true;
            $scope.selected = $scope.uvTabs[index];			
            //switchGrid();            
        };


        $scope.closeTab = function(index) {
            // todo dirty check und speichern
            $log.info('closeTab ' + index);
            $scope.uvTabs.splice(index, 1);
        };


        $scope.openHelp = function() {
        	var modalInstance = $modal.open({
                templateUrl: 'helpModal.html',
                controller: function($scope,$modalInstance) {
					$scope.ok = function() {
			            $modalInstance.close();
			        };

			        $scope.cancel = function() {
			            $modalInstance.dismiss('cancel');
			        };   
			        $scope.gotoTag = function (tag){
					    // set the location.hash to the id of
					    // the element you wish to scroll to.
					    $location.hash(tag);

					    // call $anchorScroll()
					    $anchorScroll();
					};             	
                },
                backdrop: 'static'
                
            });


        }

        // öffne UV-Dialog
        $scope.open = function() {
            var modalInstance = $modal.open({
                templateUrl: 'uvOpenModal.html',
                controller: 'OpenUvModalCtrl',
                backdrop: 'static',
                resolve: {
                    currentSelection: function() {
                        return $scope.selected;
                    }
                }
            });

            modalInstance.result.then(function(selectedItem) {                
				// wenn schon offen -> selektieren
				var found = false;
				for (var i=0; i<$scope.uvTabs.length; ++i) {
					if ($scope.uvTabs[i].bezeichnung == selectedItem.studienjahr
						&& $scope.uvTabs[i].version == selectedItem.version) {
						found = true;
						$scope.selectTab(i);
					}
				}
				if (!found) {
					$scope.busy = true;
					uvService.getUv(selectedItem.studienjahr,selectedItem.version).then(function(data) {
						if (data.data.result == 1) {
							$log.info(data.data.appdaten);
							$scope.selected = data.data.appdaten;
							$scope.addTab(data.data.appdaten);
                            // Infodaten für Charts holen
                            uvService.getInfoDaten(utilityService.subStudienjahr($scope.selected.daten.studienjahr,5), 6).then(function(infoData) {
                                if (infoData.data.result ==1) {
									$scope.selected.infoData = infoData.data.infoDaten;

                                    $scope.busy = false;
                                    $log.info(infoData); 
                                } else {
                                	$scope.busy = false;
                                    alert('Fehler beim Holen der Zusatzdaten der UV: ' + selectedItem.studienjahr);
                                }
                            });
						} else {
							$scope.busy = false;
							alert('Fehler beim Holen der UV: ' + data.data);
						}
					});
				}
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
        
        // Dialog für neue UV anlegen öffnen
        $scope.create = function() {
            var modalInstance = $modal.open({
                templateUrl: 'uvNewModal.html',
                controller: 'NewUvModalCtrl',
                backdrop: 'static',
                resolve: {
                    currentSelection: function() {
                        return $scope.selected;
                    }
                }
        });

            $log.info('neu(); selected=' + $scope.selected);

            modalInstance.result.then(function(newItem) {
                //var selectedItem = uvService.create(newItem.studienjahr,newItem.zeitraum);
				uvService.create(newItem.studienjahr,newItem.zeitraum).then(function(data) {
					if (data.data.result == 1) {
						$log.info(data.data.appdaten);
						$scope.selected = data.data.appdaten;
						$scope.addTab(data.data.appdaten);
						// Infodaten für Charts holen
						uvService.getInfoDaten(utilityService.subStudienjahr($scope.selected.daten.studienjahr,5), 6).then(function(infoData) {
							if (infoData.data.result ==1) {
								$scope.selected.infoData = infoData.data.infoDaten;

								$scope.busy = false;
								$log.info(infoData); 
							} else {
								$scope.busy = false;
								alert('Fehler beim Holen der Zusatzdaten der UV: ' + selectedItem.studienjahr);
							}
						});
					} else {
						alert('Fehler beim Erstellen der UV: ' + data.data);
					}
				});
                
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
		
		
		$scope.saveUV = function() {
			$log.info('saveUV(); selected=' + $scope.selected);
        	var anzTabs = $scope.uvTabs.length;
			for (var i=0; i<anzTabs; ++i) {
					if ($scope.uvTabs[i] == $scope.selected) {
						uvService.save($scope.uvTabs[i], false);
                        break;
					}
			}

		}
		
	    $scope.saveAsUV = function() {
			$log.info('saveAsUV(); selected=' + $scope.selected);
        	var modalInstance = $modal.open({
            templateUrl: 'uvSaveAsModal.html',
            controller: 'SaveAsUvModalCtrl',
            backdrop: 'static',
            resolve: {
                    currentSelection: function() {
                        return $scope.selected;
                    }
                }
            });
			
            $log.info('saveAsUV(); selected=' + $scope.selected);

            modalInstance.result.then(function(newItem) {
				$scope.busy = true;
				uvService.save($scope.selected, true).then(function(data) {
					if (data.data.result == 1) {
						$log.info(data.data.appdaten);
						$scope.selected = data.data.appdaten;
						$scope.addTab(data.data.appdaten);		
						// Infodaten für Charts holen
						uvService.getInfoDaten(utilityService.subStudienjahr($scope.selected.daten.studienjahr,5), 6).then(function(infoData) {
							if (infoData.data.result ==1) {
								$scope.selected.infoData = infoData.data.infoDaten;

								$scope.busy = false;
								$log.info(infoData); 
							} else {
								$scope.busy = false;
								alert('Fehler beim Holen Zusatzdaten der UV: ' + selectedItem.studienjahr);
							}
						});
					} else {
						alert('Fehler beim Speichern der UV: ' + data.data);
					}
					$scope.busy = false;
				});
                
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });

		};
	
		$scope.deleteUV = function() {
			
			if (!$scope.uvTabs.length) return;
			
            var modalInstance = $modal.open({
                templateUrl: 'uvDeleteModal.html',
                controller: 'DeleteUvModalCtrl',
                backdrop: 'static',
                resolve: {
                    currentSelection: function() {
                        return $scope.selected;
                    }
                }
            });
			
			$log.info('deleteUV(); selected=' + $scope.selected);
			
			modalInstance.result.then(function(currentSelection) {
				var anzTabs = $scope.uvTabs.length;
				for (var i=0; i<anzTabs; ++i) {
					if ($scope.uvTabs[i] == currentSelection) {
						$scope.closeTab(i);
						if ($scope.uvTabs.length > 0) {
							if (i > ($scope.uvTabs.length-1)) {
								$scope.selectTab($scope.uvTabs.length-1);
							} else {
								$scope.selectTab(i);
							}
						}
						break;
					}
				}

            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
			
		};
		
		/**
		 * Zeitraum aktualisieren. Ist der Zeitraum länger als bisher, müssen
		 * neue Basisdaten angefügt werden. Sonst werden überschüssige Jahre
		 * gelöscht (unwiederbringlich).
		 */
		$scope.updateUVZeitraum = function() {
			$log.info('updateUVZeitraum(); studienjahr=' + $scope.selected.daten.studienjahr + ', zeitraum=' + $scope.selected.daten.zeitraum);
			// aktuelle Daten speichern
			$scope.saveUV();
			// service aufrufen
			uvService.updateUvZeitraum(
				$scope.selected.daten.studienjahr,
				$scope.selected.daten.zeitraum,
				$scope.selected.version).then(function(data) {
						if (data.data.result == 1) {
							$log.info(data.data.appdaten);
							var anzTabs = $scope.uvTabs.length;
							var tabIndex = 0;
							for (var i=0; i<anzTabs; ++i) {
									if ($scope.uvTabs[i] == $scope.selected) {
										tabIndex = i;			
										break;
									}
							}
							$scope.refreshTab(data.data.appdaten,tabIndex);
							$scope.busy = false;
						} else {
							$scope.busy = false;
							alert('Fehler beim Holen der UV: ' + data.data);
						}
			});
			// ui update
			
		};

		$scope.openBDImport = function() {
            var modalInstance = $modal.open({
                templateUrl: 'bdImportModal.html',
                controller: 'ImportBDModalCtrl',
                backdrop: 'static'               
            });

            modalInstance.result.then(function(importFile) {
                $scope.importFile = importFile;
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
		
		$scope.openFOEBISImport = function() {
            var modalInstance = $modal.open({
                templateUrl: 'foebisImportModal.html',
                controller: 'ImportFOEBISModalCtrl',
                backdrop: 'static'               
            });

            modalInstance.result.then(function(importFile) {
                $scope.importFile = importFile;
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };
		

        $scope.updateSetup = function(tab) {
            $log.info('grid setup');
            if (tab.currentSetup != null) {
               $log.info('hole setup ' + tab.currentSetup.label);
               
               var gridOptions = tab.gridOptions;
			   // clear grouping
               gridOptions.groupBy();
               // clear sorting
               gridOptions.ngGrid.config.sortInfo = { fields:[], directions: [] };
                         angular.forEach(gridOptions.ngGrid.lastSortedColumns, function (c) {
                         c.sortPriority = null;
                         c.sortDirection = "";
               });
               gridOptions.ngGrid.lastSortedColumns = [];
               gridOptions.ngGrid.config.sortInfo = { fields:[], directions: [], columns:[] };

               // Setup holen und grid aktualisieren
			   uvService.getSetup(tab.currentSetup.label).then(
				   function(data) {
					   
					   $timeout(function() {
						    var setupData = data.data.setupList;
							for (var j=0; j<setupData.length; j++) {
								var columns = gridOptions.$gridScope.columns;
								var anz = columns.length;

								for( var i=0; i<anz; i++) {
									if (columns[i].field !== "") {

										 if (columns[i].field === setupData[j].field) {
											 columns[i].width = setupData[j].width;
											 columns[i].visible = setupData[j].visible;
											 
											 //columns[i].sortPriority = setupData[j].sortPriority;
											 //columns[i].isGroupedBy = setupData[j].isGroupedBy;
											 //columns[i].groupIndex = setupData[j].groupIndex;
											 //columns[i].index = setupData[j].index;
											 if (setupData[j].sortDirection && !setupData[j].isGroupedBy) {
												 //gridOptions.sortBy(setupData[j].field);
												 columns[i].sortDirection = setupData[j].sortDirection;
												 if (setupData[j].sortPriority !== undefined) {
													 columns[i].sort({ shiftKey:true });
												 } else {
													 columns[i].sort();
												 }												 
											 }
											 if (setupData[j].isGroupedBy) {
												 gridOptions.groupBy(setupData[j].field);
											 }

											 break;
										 }
									 }
								 }
							}
							 if (!$scope.$$phase) {
								$scope.$digest();
							}
					   }); // timeout
					   
				   });
			   
			   
               
			   $log.info('setupt update ende');
            } else {
				// Reset
				var gridOptions = tab.gridOptions;
				gridOptions.groupBy();
				// clear sorting
                gridOptions.ngGrid.config.sortInfo = { fields:[], directions: [] };
                         angular.forEach(gridOptions.ngGrid.lastSortedColumns, function (c) {
                         c.sortPriority = null;
                         c.sortDirection = "";
               });
               gridOptions.ngGrid.lastSortedColumns = [];
               gridOptions.ngGrid.config.sortInfo = { fields:[], directions: [], columns:[] };
			   // visible
			   var unsichtbar = ['beginn','ende','aufnahme','regelstudiendauer','bewerber','dropout','interessenten','studierende','foebisAktive'];
							   
			   var columns = gridOptions.$gridScope.columns;
			   var anz = columns.length;

			   for( var i=0; i<anz; i++) {
					if (unsichtbar.indexOf(columns[i].field) !== -1) {
						columns[i].visible = false;
					} else {
						columns[i].visible = true;
					}
					
			   }
			   
			}
        }

		$scope.saveSetup = function(tab) {
			var modalInstance = $modal.open({
                templateUrl: 'setupSaveModal.html',
                controller: 'SetupModalCtrl',
                backdrop: 'static',
				resolve: {
                    currentSetup: function() {
                        return tab.currentSetup;
                    },
					gridOptions: function() {
                        return tab.gridOptions;
                    }
                }
            });

            modalInstance.result.then(function(bezeichnung) {
                $scope.bezeichnung = bezeichnung;
				
				// Liste mit Einstellungen aktualisieren
				uvService.getSetupList().then(
					function(response) {
						if (response.data.result == 1) {
							//$scope.setupOptions = response.data.setupList;
							// merge notwendig, weil sonst alle selects auf
							// ihre Auswahl verlieren
							$scope.mergeSetupOptions(response.data.setupList);
							if (bezeichnung) {
								// durch Aktualisierung geht Auswahl verloren								
								for (var i=0; i < $scope.setupOptions.length; ++i) {
									if ($scope.setupOptions[i].label === bezeichnung) {
										//$timeout(function() {
											tab.currentSetup = $scope.setupOptions[i];
											//tab.currentSetup = $scope.setupOptions[1];
										//});										
										break;
									}
								}
								
							}
						} else {
							$log.warn("Konnte Liste mit Einstellungen nicht laden");
						}
					}, 
					function() {
						$log.warn("Konnte Liste mit Einstellungen nicht laden");
					}
				);
				
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
		};
		
		$scope.deleteSetup = function(tab) {
			var modalInstance = $modal.open({
                templateUrl: 'setupDeleteModal.html',
                controller: 'SetupDeleteModalCtrl',
                backdrop: 'static',
				resolve: {
                    currentSetup: function() {
                        return tab.currentSetup;
                    }
                }
            });

            modalInstance.result.then(function(bezeichnung) {
                $scope.bezeichnung = bezeichnung;
				var index = $scope.setupOptions.indexOf(tab.currentSetup);
				$scope.setupOptions.splice(index, 1);									
            }, function() {
                $log.info('Modal dismissed at: ' + new Date());
            });
		};

		$scope.downloadUV = function() {
			if ($scope.selected != undefined) {
				$log.info('Download UV ');
				window.location = "../api.php?endpoint=exportXML&studienjahr=" + encodeURIComponent($scope.selected.daten.studienjahr) + "&version=" + $scope.selected.version ;
			}
		};

		$scope.exportCSV = function() {
			if ($scope.selected != undefined) {
				$log.info('Export CSV');
				window.location = "../api.php?endpoint=exportCSV&studienjahr=" + encodeURIComponent($scope.selected.daten.studienjahr) + "&version=" + $scope.selected.version ;
			}
		};

        $scope.updateGrouping = function() {
            $log.debug('updateGrouping:' + $scope.groupColumns);
        };
        
        // handle cell edits
        $scope.afterCellEdit = function(e, args) {
            alert("Cell (" + args.cell.rowIndex() + ", " + args.cell.cellIndex() + ") has been edited.");
        };

        $scope.currentCellChanging = function(e, args) {
            var col = args.cellIndex;
            var row = args.rowIndex;
            //alert("Selected Row " + row);
        };

}])


/* Controller fuer UV Oeffnen Dialog */
.controller("OpenUvModalCtrl", ['$scope', '$modalInstance', 'currentSelection',
    'uvService', 'UtilityService','$log', function($scope, $modalInstance, 
	currentSelection, uvService, utilityService, $log) {

        $log.info('currentSelection=' + currentSelection);
		$scope.busy = true;
		// Liste Metadaten
		$scope.metaData = [];
		// Liste Studienjahre
		$scope.studienjahr = [];
		// Liste der UVs (nur Metadaten wie Iteration, Studienjahr, ..)
		$scope.items = [];
		// im Pull-Down ausgewähltes Studienjahr
        //$scope.sjSelected = (currentSelection !== undefined && currentSelection.daten ? currentSelection.daten.studienjahr : null);
		// Auswahl der UV
		$scope.selectedItem = null;

		$scope.form = {
			studienjahr: (currentSelection !== undefined && currentSelection.daten ? currentSelection.daten.studienjahr : null)
		};

		// Liste mit Iterationen aktualisieren
        $scope.updateItems = function() {
			if ($scope.form.studienjahr !== undefined) {
				$scope.items = uvService.iterationen($scope.form.studienjahr, $scope.metaData);
			}
            $scope.selectedItem = null;
		};

		$scope.$watch('form.studienjahr', function(newValue, oldValue) {
			$scope.updateItems();
			//$scope.updateItems();
		});
            
        // vorhandene Studienjahre und UV-Iterationen holen
        var promise = uvService.studienjahr();
		promise.then(function(metaData) {
				$scope.metaData = metaData;
				var currentSJ = utilityService.currentStudienjahr();
				if (!$scope.sjSelected) {
					$scope.sjSelected = currentSJ;
				}
			   // Studienjahr in Array kopieren
				for (var i=0; i<metaData.length; ++i) {
					$scope.studienjahr.push(metaData[i].studienjahr);
				}				
				// UV Iterationen filtern
				$scope.updateItems();
				$scope.busy = false;
		}, function(reason) {
			$scope.busy = false;
			alert('Failed: ' + reason);				
		});
        
        
        
		
		$scope.updateAuswahl = function(i) {
			$scope.selectedItem = i;
		};

        $scope.ok = function() {
            $modalInstance.close({
				studienjahr: $scope.form.studienjahr,
				version: $scope.selectedItem.nr
			});
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
  }])
  
/* Controller fuer UV Neu Dialog */
.controller("NewUvModalCtrl", ['$scope', '$modalInstance', 'currentSelection',
    'uvService', 'UtilityService', '$log', function($scope, $modalInstance, 
    currentSelection, uvService, utilityService, $log) {

        $log.info('currentSelection=' + currentSelection);
		$scope.busy = true;

        // vorhandene Semester holen, clonen und um Folgesemester erweitern
        var promise = uvService.studienjahr();
		$scope.studienjahr = [];
		promise.then(function(metaData) {				
			   // Studienjahr in Array kopieren
				for (var i=0; i<metaData.length; ++i) {
					$scope.studienjahr.push(metaData[i].studienjahr);
				}
				if ($scope.studienjahr.length > 0) {
					$scope.studienjahr.push(
						utilityService.incStudienjahr(
							$scope.studienjahr[$scope.studienjahr.length - 1]));
				} else {
					// Liste mit aktuellem und folgenden Studienjahr init			
					var currentSJ = utilityService.currentStudienjahr();
					$scope.studienjahr.push(currentSJ);
					$scope.studienjahr.push(utilityService.incStudienjahr(currentSJ));
				}
				$scope.busy = false;
		}, function(reason) {
			$scope.busy = false;
			alert('Failed: ' + reason);				
		});     

        // evt. vorhandene Auswahl beruecksichtigen
        $scope.neu = {
            studienjahr: (currentSelection !== undefined && currentSelection.daten.studienjahr ? currentSelection.daten.studienjahr : $scope.studienjahr[0]),
            zeitraum: 2
        };

        $scope.ok = function() {
            $modalInstance.close($scope.neu);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
  }])
  
.controller("DeleteUvModalCtrl", ['$scope', '$modalInstance', 'currentSelection',
    'uvService', 'UtilityService','$log', function($scope, $modalInstance, 
	currentSelection, uvService, utilityService, $log) {
		
		$scope.selected = currentSelection;
		
		$scope.ok = function() {
			
			uvService.delete(currentSelection.bezeichnung,currentSelection.version).then(function(data) {
					if (data.data.result == 1) {	
						$modalInstance.close(currentSelection);						
					} else {
						alert('Fehler beim Löschen der UV: ' + data.data);
					}
			}, function(reason) {				
				alert('Failed: ' + reason);				
			});
			          
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };

  }])
.controller("SaveAsUvModalCtrl", ['$scope', '$modalInstance', 'currentSelection',
    'uvService', 'UtilityService','$log', function($scope, $modalInstance, 
	currentSelection, uvService, utilityService, $log) {
		
		$scope.selected = currentSelection;
		
		$scope.ok = function() {
			$log.info('SAVE AS OK')  
			$modalInstance.close();
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };

  }])
 
  
  /* Controller fuer BD Import  Dialog */
.controller("ImportBDModalCtrl", ['$scope', '$modalInstance', 
    'uvService', 'UtilityService', '$log', '$fileUpload', function($scope, $modalInstance, 
    uvService, utilityService, $log, $fileUpload) {

    	$scope.fileData = { myFile : null};

		$scope.busy = false;
    			
        $scope.ok = function() {
			var file = $scope.fileData.myFile;
	        console.log('file is ' + JSON.stringify(file));
	        var uploadUrl = "../api.php?endpoint=importBD";
			$scope.busy = true;
	        var promise = $fileUpload.uploadFileToUrl(file, uploadUrl);
			promise.then(function(greeting) {				
				$scope.busy = false;
				$modalInstance.close($scope.myFile);
			}, function(reason) {
				$scope.busy = false;
				alert('Failed: ' + reason);				
			});            
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
  }])
  
  // Controller für Import der FÖBis-Daten vom Webservice der AQA
  .controller("ImportFOEBISModalCtrl", ['$scope', '$modalInstance', 
    'uvService', 'UtilityService', '$log', '$fileUpload', function($scope, $modalInstance, 
    uvService, utilityService, $log, $fileUpload) {

    	// Liste mit Studienjahren erzeugen
		$scope.studienjahr = [];
		var currentSJ = utilityService.currentStudienjahr();
		var i = '2009/10';
		while (i != currentSJ) {			
			$scope.studienjahr.push(i);
			i = utilityService.incStudienjahr(i);
		}
		$scope.studienjahr.push(i);
		i = utilityService.incStudienjahr(i);
		
		$scope.foebis = {
			studienjahr: currentSJ
		}
		
		$scope.busy = false;
    			
        $scope.ok = function() {
			$scope.busy = true;
	        var promise = uvService.importFOEBis($scope.foebis.studienjahr);
			promise.then(function(greeting) {				
				$scope.busy = false;
				$modalInstance.close($scope.myFile);
			}, function(reason) {
				$scope.busy = false;
				alert('Failed: ' + reason);				
			});            
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
  }])
  
  
.controller("SetupModalCtrl", ['$scope', '$modalInstance', 
    'uvService', 'UtilityService', '$log', 'currentSetup','gridOptions', function($scope, $modalInstance, 
    uvService, utilityService, $log, currentSetup, gridOptions) {

    	$scope.bezeichnungDTO = {
			bezeichnung: ( (currentSetup && currentSetup.label !== "") ? currentSetup.label : "")			
		};  // aktuelle Auswahl?

		$scope.busy = false;
    			
        $scope.ok = function() {
			
			// speichern            
			$scope.busy = true;
	        var promise = uvService.saveSetup($scope.bezeichnungDTO.bezeichnung,gridOptions);
			promise.then(function(data) {	
				$scope.busy = false;
				if (data.data.result == 1) {	
						$modalInstance.close($scope.bezeichnungDTO.bezeichnung);
				} else {
						alert('Fehler beim Speichern der Einstellungen: ' + data.data);
				}								
			}, function(reason) {
				$scope.busy = false;
				alert('Failed: ' + reason);				
			});            
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
  }])  
.controller("SetupDeleteModalCtrl", ['$scope', '$modalInstance', 
    'uvService', 'UtilityService', '$log', 'currentSetup', function($scope, $modalInstance, 
    uvService, utilityService, $log, currentSetup) {

    	$scope.currentSetup = currentSetup;
		$scope.busy = false;
    			
        $scope.ok = function() {
			
			// speichern            
			$scope.busy = true;
	        var promise = uvService.deleteSetup($scope.currentSetup.label);
			promise.then(function(data) {	
				$scope.busy = false;
				if (data.data.result == 1) {	
						$modalInstance.close($scope.bezeichnung);
				} else {
						alert('Fehler beim Löschen der Einstellung: ' + data.data);
				}								
			}, function(reason) {
				$scope.busy = false;
				alert('Failed: ' + reason);				
			});            
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
  }])    
.controller("SummaryCtrl", ['$scope', '$window', '$modal', '$log', 'uvService', 'UtilityService',
    function($scope, $window, $modal, $log, uvService, utilityService) {
        $scope.columns = $scope.selected.studienjahre; //['2013/14', '2014/15', '2015/16', '2016/17','2017/18'];
        $scope.rows = ['BB','VZ'];
        $scope.cells = {};
        $scope.compute = function(cell) {
            //return $parse($scope.cells[cell])($scope);
            return '123,5';
        };  
		// parseInt function für Template {{}}
		$scope.parseInt = parseInt;

    }]);

