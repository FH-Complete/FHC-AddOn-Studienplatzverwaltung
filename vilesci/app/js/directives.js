'use strict';

/* Directives */


angular.module('app.directives', []).
  directive('appVersion', ['version', function(version) {
    return function(scope, elm, attrs) {
      elm.text(version);
    };
  }]).
        
		
  directive('chartGrid',['$window', function($window) {
	return {
		restrict: 'E',
        replace: true,
        scope: {
			dataset: '=',
			labels: '='
		},        
        template: '<svg  class="graph" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" ng-attr-width="{{graph.width}}" ng-attr-height="{{graph.height + 65}}" style="background:#efefef">' + 					
					'<g ng-attr-transform="{{offsetTemplate}}" style="opacity:1;">\n' +
						'<g class="grid x-grid" id="xGrid" >\n'+
							'<line ng-repeat="xLabel in labels" ng-attr-x1="{{x($index)}}" ng-attr-x2="{{x($index)}}" y1="0" ng-attr-y2="{{graph.height}}"></line>'+													
						'</g>\n'+
						'<text ng-repeat="xLabel in labels" ng-attr-transform="rotate(-35,{{x($index)}},{{graph.height + graph.marginTop + 5}})" ng-attr-x="{{x($index)}}" ng-attr-y="{{graph.height + graph.marginTop + 5}}" dy=".32em" text-anchor="end">{{labels[$index]}}</text>'+						
						'<g class="grid y-grid" id="yGrid">\n' +
							'<line ng-repeat="yVal in yAxis" x1="0" ng-attr-x2="{{graph.width-graph.legendeWidth-graph.axisWidth}}" ng-attr-y1="{{yVal.wert}}" ng-attr-y2="{{yVal.wert}}"></line>' +						
						'</g>\n'+
                    '</g>\n' +
                    '<g ng-attr-transform="translate(0,{{graph.marginTop}})" >' +
						'<g id="yLabels">'+
							'<text ng-repeat="yVal in yAxis" x="40" ng-attr-y="{{yVal.wert}}" dy=".32em" text-anchor="end">{{yVal.label}}</text>' +
						'</g>\n' +
                    '</g>'+
					'<g ng-attr-transform="{{offsetTemplate}}" style="opacity:1;">' +
						'<polyline ng-repeat="data in graph.lineData" ng-attr-points="{{data.ld}}"  style="stroke:{{data.color}};stroke-width:2;fill:none" /> ' +
					'</g>'+
					'<g ng-attr-transform="{{offsetLegende}}" style="opacity:1;">' +
						'<line ng-repeat-start="data in graph.lineData" x1="10" ng-attr-y1="{{$index*15}}" x2="20" ng-attr-y2="{{$index*15}}" style="stroke:{{data.color}};stroke-width:4;fill:none" /> ' +
						'<text ng-repeat-end x="25" ng-attr-y="{{$index*15}}" dy=".32em" text-anchor="start">{{data.label}}</text>' +
					'</g>'+
					'Sorry, your browser does not support inline SVG.  ' +
				  '</svg>\n',
		link: function(scope, element, attrs) {
			// Default Schrittweite für Y-Achse
			var axisInterval = 20;
			
			// Schrittweiten für Y-Achse
			var interval = [5, 10, 20, 50];
			scope.graph = { height: 155,  width: 1000, axisWidth: 50, marginTop: 10, legendeWidth: 120};

			function getAxisInterval(intervalIndex) {			
				if (max/interval[intervalIndex] > 12 && intervalIndex <= interval.length) {
					return getAxisInterval(intervalIndex + 1);
				}
				return interval[intervalIndex];
			}
	
			function getMax(datasets) {
                var max = 0;
				for (var i = 0; i < datasets.length; i++) {
                    max = Math.max(max,Math.max.apply(null, datasets[i].data));
				}
                return max;
			}

			// höchsten Wert in Dataset holen
			var max = getMax(scope.dataset); //Math.max.apply(null, scope.dataset.data);
			axisInterval = getAxisInterval(0);
			
			
			//scope.xLabels = ['WS2009','SS2010','WS2010','SS2011','WS2011','SS2012','WS2012','SS2013','WS2013','SS2014', 'WS2014', 'SS2015', 'WS2015', 'SS2016', 'WS2016', 'SS2017'];
			//scope.dataset = [      70,      80,      90,      85,      75,      80,      82,     90,      93,      101,     90,        80,        83,     89,      98,        100];
			scope.width = function() {
				var dataPoints = scope.labels.length;
				return (scope.graph.width-scope.graph.axisWidth-scope.graph.legendeWidth) / (dataPoints-1);
			}
            scope.offsetTemplate = "translate(" + scope.graph.axisWidth + "," + scope.graph.marginTop + ")";	
			scope.offsetLegende = "translate(" + (scope.graph.width-scope.graph.legendeWidth) + "," + scope.graph.marginTop + ")";	
			scope.calcYAxis = function(data) {
				// höchsten Wert in Dataset holen
				//var max = Math.max.apply(null, scope.dataset.data);
				// nächst höherer 10er Wert
				var max10 = (Math.floor(max / axisInterval) + 1) * axisInterval;
				if (max10 <=0) return [];
				var yVal = new Array(max10/axisInterval + 1);
				var y = max10;
				for(var i = 0;i <= (max10/axisInterval); i++) {
					yVal[i] = { wert: (i*axisInterval) / max10 * (scope.graph.height), label: y};
					y -= axisInterval;					
				}
				return yVal;
			}
			scope.yAxis = scope.calcYAxis(scope.dataset.data);
			// X-Koordinaten 
			scope.x = function(index) {
				return index * scope.width();
			};
			scope.generateLineData = function(datasetRow) {
				var lineStr = '';
				// nächst höherer 10er des größten Werts aller Daten
				var max10 = (Math.floor(max / axisInterval) + 1) * axisInterval;
				
				if (datasetRow.style == "Stufen") {
					// Stufenchart; horizontale Linie bis sich Wert ändert
					var lastI = null;
					for (var i = 0; i < datasetRow.data.length; ++i) {
						if (datasetRow.data[i] != 0) {
							if (i > 0 && lastI != null) {
								if (lineStr == "") {
									lineStr = scope.x(datasetRow.offset) + ','+ (scope.graph.height-datasetRow.data[lastI] / max10 * scope.graph.height) + ' ';
								}
								lineStr = lineStr +
									// horizontale Linie
									scope.x(i + datasetRow.offset) + ','+ (scope.graph.height-datasetRow.data[lastI] / max10 * scope.graph.height) + ' ' +
									// vertikale Linie
									scope.x(i + datasetRow.offset) + ','+ (scope.graph.height-datasetRow.data[i] / max10 * scope.graph.height) + ' ';
							} 
							lastI = i;
							
						}
					}
					// horizontale Linie wenn letzter Wert nicht vorhanden
					if (datasetRow.data[datasetRow.data.length-1] == 0 && lastI != null) {
						lineStr += scope.x(datasetRow.data.length-1 + datasetRow.offset) + ','+ (scope.graph.height-datasetRow.data[lastI] / max10 * scope.graph.height);
					}
					
				} else {
					// Default: Linienchart; einfach Punkte miteinander Verbinden
					for (var i = 0; i < datasetRow.data.length; ++i) {
						lineStr += scope.x(i + datasetRow.offset) + ','+ (scope.graph.height-datasetRow.data[i] / max10 * scope.graph.height) + ' ';
					}
				}
				return lineStr;
			};
			// Aktualisieren wenn Daten geändert wurden
	        scope.$watch('dataset',function() {
				max = getMax(scope.dataset);
			    axisInterval = getAxisInterval(0);
				scope.yAxis = scope.calcYAxis(scope.dataset.data);
				scope.graph.lineData = [];
				for (var i = 0; i < scope.dataset.length; i++) {
					scope.graph.lineData[i] = 
						{
							//ld: scope.generateLineData(scope.dataset[i].data, scope.dataset[i].offset),
							ld: scope.generateLineData(scope.dataset[i]),
							color: scope.dataset[i].color,
							label: scope.dataset[i].label
						};
				}                
            });
			
			// Browser onresize event
			window.onresize = function() {
			  scope.$apply();
			};
			// Watch for resize event
			scope.$watch(function() {
				return angular.element($window)[0].innerWidth;
			}, function() {
				//scope.render(scope.data);
			});
		}
	}	  
  }]).
		
		
  directive('barChart', ['$window', function($window) {
    return {
	  restrict: 'EA',
      scope: {},
      link: function(scope, element, attrs) {
		  
		var margin = parseInt(attrs.margin) || 20,
            barHeight = parseInt(attrs.barHeight) || 20,
            barPadding = parseInt(attrs.barPadding) || 5;
		          
		var svg = d3.select(element[0])
            .append("svg")
            .style('width', '100%');
		
		// Browser onresize event
		window.onresize = function() {
		  scope.$apply();
		};

		// hard-code data
		scope.data = [
		  {name: "Greg", score: 98},
		  {name: "Ari", score: 96},
		  {name: 'Q', score: 75},
		  {name: "Loser", score: 48}
		];

		// Watch for resize event
		scope.$watch(function() {
			return angular.element($window)[0].innerWidth;
		}, function() {
			scope.render(scope.data);
		});

		scope.render = function(data) {			
			svg.selectAll('*').remove();			
			if (!data || d3.select(element[0]).node().offsetWidth == 0) return;
			
			// setup variables
			var width = d3.select(element[0]).node().offsetWidth - margin,
				// calculate the height
				height = scope.data.length * (barHeight + barPadding),
				// Use the category20() scale function for multicolor support
				color = d3.scale.category20(),
				// our xScale
				xScale = d3.scale.linear()
				  .domain([0, d3.max(data, function(d) {
					return d.score;
				  })])
				  .range([0, width]);

			// set the height based on the calculations above
			svg.attr('height', height);

			//create the rectangles for the bar chart
			svg.selectAll('rect')
			  .data(data).enter()
				.append('rect')
				.attr('height', barHeight)
				.attr('width', 140)
				.attr('x', Math.round(margin/2))
				.attr('y', function(d,i) {
				  return i * (barHeight + barPadding);
				})
				.attr('fill', function(d) { return color(d.score); })
				.transition()
				  .duration(300)
				  .attr('width', function(d) {
					return xScale(d.score);
				  });
		}  // render

		
      }}
  }]).
		
  
  directive('multiselectDropdown', [function() {
    return function(scope, element, attributes) {
        
        element = $(element[0]); // Get the element as a jQuery element
        
        // Below setup the dropdown:
        
        element.multiselect({
            buttonClass : 'btn',
            //buttonWidth : '200px',
            buttonContainer : '<div class="btn-group" />',
            maxHeight : 200,
            enableFiltering : false,
            enableCaseInsensitiveFiltering: false,
            buttonText : function(options) {
                if (options.length == 0) {
                    return element.data()['placeholder'] + ' <b class="caret"></b>';
                } else if (options.length > 1) {
                    return options[0].text 
                    + ' + ' + (options.length - 1)
                    + ' weiteres ausgewählt <b class="caret"></b>';
                } else {
                    return options[0].text
                    + ' <b class="caret"></b>';
                }
            },
            // Replicate the native functionality on the elements so
            // that angular can handle the changes for us.
            onChange: function (optionElement, checked) {
                if (checked) {
                    optionElement.attr('selected', 'selected');
                } else {
                    optionElement.removeAttr('selected');
                }
                element.change();
            }
            
        });
        // Watch for any changes to the length of our select element
        scope.$watch(function () {
            return element[0].length;
        }, function () {
            element.multiselect('rebuild');
        });
        
        // Watch for any changes from outside the directive and refresh
        scope.$watch(attributes.ngModel, function () {
             element.multiselect('refresh');
        });
        
        // Below maybe some additional setup
    };
}])

.directive('resize', function ($window) {
    return function (scope, element) {

            var w = angular.element($window);
            scope.getWindowDimensions = function () {
                return { 'h': w.height(), 'w': w.width() };
            };
            scope.$watch(scope.getWindowDimensions, function (newValue, oldValue) {

                // resize Grid to optimize height
                $('.gridStyle').height(newValue.h - 479);
            }, true);

            w.bind('resize', function () {
                scope.$apply();
            });
    }
})

.directive('vTabs', function($window) {
    return {
      restrict: 'E',
      transclude: true,
      scope: {},
      controller: function($scope) {
        var panes = $scope.panes = [];
 
        $scope.select = function(pane) {
          angular.forEach(panes, function(pane) {
            pane.selected = false;
          });
          pane.selected = true;
        };
 
        this.addPane = function(pane) {
          if (panes.length == 0) {
            $scope.select(pane);
          }
          panes.push(pane);
        };
      },
      templateUrl: 'vtabs.html'
      
      
    };
  })
  .directive('vPane', function() {
    return {
      require: '^vTabs',
      restrict: 'E',
      transclude: true,
      scope: {
        title: '@'
      },
      link: function(scope, element, attrs, tabsCtrl) {
        tabsCtrl.addPane(scope);
      },
      templateUrl: 'vpane.html'
    };
  })
  .directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        scope:false,
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
            
            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);
