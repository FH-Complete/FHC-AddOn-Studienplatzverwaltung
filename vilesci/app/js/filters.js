'use strict';

/* Filters */

angular.module('app.filters', []).
  filter('interpolate', ['version', function(version) {
    return function(text) {
      return String(text).replace(/\%VERSION\%/mg, version);
    }
}]).
  filter('checkmark', function() {
  return function(input) {
    return input ? '\u2713' : '\u2718';
  };
}).
filter('sumBySemesterAndOrgForm', function() {
  return function(input, semester, orgForm, attribut) {
	  var sum = 0;	  
	  for (var i=0; i<input.length; ++i) {
		  if (input[i].studiensemester === semester && input[i].orgForm === orgForm) {
			  sum = sum + (input[i][attribut] !== undefined && input[i][attribut] !=null && input[i][attribut] != "" ? parseInt(input[i][attribut], 10) : 0);
		  }
	  }
	  return sum;
  };
}).
filter('diffBySemesterAndOrgForm', function(sumBySemesterAndOrgFormFilter) {
  return function(input, semester, orgForm, attribut1, attribut2) {
    var sum1 = sumBySemesterAndOrgFormFilter(input, semester, orgForm, attribut1);
    var sum2 = sumBySemesterAndOrgFormFilter(input, semester, orgForm, attribut2);    
    return (sum1 - sum2);
  };
});	