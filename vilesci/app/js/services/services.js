'use strict';

/* Services */

angular.module('app.services', []).
	value('version', '0.3').
	service('uvService', function(UtilityService, $q, $http) {
	// Dummy Liste der vorhandenen Semester
	var studienjahrFixture = [
		// This is private
		'2012/13', '2013/14'
	];
	// Dummy Liste der für jedes Jahr vorhandenen Iterationen
	var iterationenFixture = [		
			{
				studienjahr: '2012/13', 
			    uvListe: [{nr: 1, lastupdate: '1.6.2012', notizen: '', status:'entwurf', zeitraum:4},
                          {nr: 2, lastupdate: '9.6.2012', notizen: '', status:'eingereicht', zeitraum:4}]
			},
			{
				studienjahr: '2013/14', 
			    uvListe: [
							{nr: 1, lastupdate: '1.12.2012', notizen: '', status:'entwurf', zeitraum:4},
							{nr: 2, lastupdate: '9.12.2012', notizen: '', status:'eingereicht', zeitraum:4}
				         ]
			}	
	];
	// nur zum Testen verwendet
    var uvFixture = [
        {studienjahr: '2012/13', 
         iterationen: [
             {nr:1, lastupdate: '2.8.2012', zeitraum:4,status:'entwurf',
              gesamtDaten:
                    [
                      {"stgKz": "0227", "stgBezeichnung": "BBE", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123, "bewerber": 711, "interessenten": 654, "dropout": 567, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 567, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 567, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 567, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 567, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30}
                          ]
                      },
                      {"stgKz": "0228", "stgBezeichnung": "MBE", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 654, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0}
                          ]
                      },
                      {"stgKz": "0254", "stgBezeichnung": "BEL", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 105, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 106, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 107, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 108, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 109, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5}
                          ]
                      },
                      {"stgKz": "0255", "stgBezeichnung": "BEW", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0256", "stgBezeichnung": "BWI", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0257", "stgBezeichnung": "BIF", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                          ]},
                      {"stgKz": "0258", "stgBezeichnung": "BIC", "stgArt": "Ba", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                          ]},
                      {"stgKz": "0297", "stgBezeichnung": "MES", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0298", "stgBezeichnung": "MTI", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0299", "stgBezeichnung": "MSE", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0300", "stgBezeichnung": "MIE", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0301", "stgBezeichnung": "MTM", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0302", "stgBezeichnung": "MWI", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0303", "stgBezeichnung": "MIC", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0327", "stgBezeichnung": "BST", "stgArt": "Ba", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0328", "stgBezeichnung": "MST", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 123,"bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]}
                  ]
             },
             {nr:2, lastupdate: '5.8.2012', zeitraum:4,status:'eingereicht',
             gesamtDaten:
                    [
                      {"stgKz": "0227", "stgBezeichnung": "BBE", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 160, "gpzUv": 200, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": 173, "gpzBdTolOben": 207, "simGpzUv": 190, "simGpzDiff": 30, "npzBd": 160, "npzUv": 190, "npzDiff": 30}
                          ]
                      },
                      {"stgKz": "0228", "stgBezeichnung": "MBE", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 30, "gpzUv": 30, "gpzDiff": 0, "gpzBdTfp": 3, "gpzBdTolUnten": 27, "gpzBdTolOben": 33, "simGpzUv": 30, "simGpzDiff": 0, "npzBd": 30, "npzUv": 30, "npzDiff": 0}
                          ]
                      },
                      {"stgKz": "0254", "stgBezeichnung": "BEL", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 105, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 105, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 105, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 105, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 105, "gpzUv": 100, "gpzDiff": -5, "gpzBdTfp": 10, "gpzBdTolUnten": 90, "gpzBdTolOben": 100, "simGpzUv": 100, "simGpzDiff": -5, "npzBd": 105, "npzUv": 100, "npzDiff": -5}
                          ]
                      },
                      {"stgKz": "0255", "stgBezeichnung": "BEW", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 166, "gpzUv": 180, "gpzDiff": 14, "gpzBdTfp": 17, "gpzBdTolUnten": 176, "gpzBdTolOben": 210, "simGpzUv": 193, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0256", "stgBezeichnung": "BWI", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0257", "stgBezeichnung": "BIF", "stgArt": "Ba", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                          ]},
                      {"stgKz": "0258", "stgBezeichnung": "BIC", "stgArt": "Ba", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                          ]},
                      {"stgKz": "0297", "stgBezeichnung": "MES", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0298", "stgBezeichnung": "MTI", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0299", "stgBezeichnung": "MSE", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0300", "stgBezeichnung": "MIE", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0301", "stgBezeichnung": "MTM", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0302", "stgBezeichnung": "MWI", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122,  "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 147, "gpzUv": 115, "gpzDiff": -32, "gpzBdTfp": 11, "gpzBdTolUnten": 102, "gpzBdTolOben": 124, "simGpzUv": 113, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0303", "stgBezeichnung": "MIC", "stgArt": "Ma", "orgForm": "VZ", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 184, "gpzUv": 210, "gpzDiff": 26, "gpzBdTfp": 18, "gpzBdTolUnten": 186, "gpzBdTolOben": 222, "simGpzUv": 204, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0327", "stgBezeichnung": "BST", "stgArt": "Ba", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 183, "gpzUv": 180, "gpzDiff": -3, "gpzBdTfp": 16, "gpzBdTolUnten": 164, "gpzBdTolOben": 196, "simGpzUv": 180, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]},
                      {"stgKz": "0328", "stgBezeichnung": "MST", "stgArt": "Ma", "orgForm": "BB", 
                          studiengangDaten:[
                              {"studiensemester": "WS2012", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2013", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "SS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27},
                              {"studiensemester": "WS2014", "studierende": 122, "bewerber": 711, "interessenten": 123, "dropout": 5, "gpzBd": 57, "gpzUv": 60, "gpzDiff": 3, "gpzBdTfp": 6, "gpzBdTolUnten": 54, "gpzBdTolOben": 66, "simGpzUv": 60, "simGpzDiff": 27, "npzBd": 166, "npzUv": 193, "npzDiff": 27}
                          ]}
                  ]
              }  
         ]   // Iterationen WS2009
        }
        ,
        {studienjahr: '2013/14', 
         iterationen: [{nr:1, lastupdate: '2.8.2013', zeitraum:4}]
        }

    ];
    
    function generateFakeStudiengangDaten(sem, zeitraum) {
        var studiengangDaten = [];
        var tempSemester = sem;
        var last = UtilityService.addSemester(tempSemester, zeitraum);
        while(tempSemester !== last) {
            studiengangDaten.push(
                {"studiensemester": tempSemester, "studierende" : Math.floor((Math.random()*100)+1),
				 "bewerber": Math.floor((Math.random()*100)+1), 
                 "interessenten": Math.floor((Math.random()*100)+1),
                 "dropout": Math.floor((Math.random()*10)+1), "gpzBd": Math.floor((Math.random()*100)+1),
                 "gpzUv": 0, "gpzDiff": 40, "gpzBdTfp": 17, "gpzBdTolUnten": Math.floor((Math.random()*200)+1),
                 "gpzBdTolOben": Math.floor((Math.random()*200)+1),
				 //"simGpzUv": 0, "simGpzDiff": Math.floor((Math.random()*30)+1),
                 "npzBd": Math.floor((Math.random()*100)+1), "npzUv": 0,
                 "npzDiff": Math.floor((Math.random()*100)+1)}
            );
            tempSemester = UtilityService.incSemester(tempSemester);
        }
        return studiengangDaten;
    }
    
	return {
		studienjahr: function() {
			var deferred = $q.defer();	
			
			$http.get("../api.php?endpoint=getMetadata")
			.success(function(data, status, headers, config){			
				if (data.result === 0) {
					console.debug("Studienjahre holen Fehler: " + data.errormsg);
					deferred.reject(data.errormsg);
				} else {
					console.debug("Studienjahre geholt");
					deferred.resolve(data);
				}
			})
			.error(function(data, status, headers, config) {			
				console.debug("Fehler beim holen der Studienjahre [" + status + "]");	
				deferred.reject('Fehler beim holen der Studienjahre');
			});
			return deferred.promise;
			//
			//return studienjahrFixture;
		},
		
		iterationen: function(sj,metaData) {
			for (var index = 0; index < metaData.length; ++index) {
				if (metaData[index].studienjahr === sj) {
					return metaData[index].uvListe;
				}
			}			
			return false;			
		},
		
		iterationenFake: function(sj) {
			for (var index = 0; index < iterationenFixture.length; ++index) {
				if (iterationenFixture[index].studienjahr === sj) {
					return iterationenFixture[index].uvListe;
				}
			}			
			return false;			
		},
		 /**
         * UV holen
         * 
         * @param {string} studienjahr
         * @param {number} iterationNr Nummer der Iteration
         * @returns {UV} Promise die bei Erfolg Datenstruktur mit UV Iteration
		 * liefert
         */
		getUv: function(_sj, _version) {
           return $http.get("../api.php?endpoint=loadUV&studienjahr=" + encodeURIComponent(_sj) + "&version=" + _version );
		},
        /**
         * Zusatzdaten holen die für Chart benötigt werden. 
         */
        getInfoDaten: function(_sj,_zeitraum) {
           return $http.get("../api.php?endpoint=getInfoDaten&studienjahr=" + encodeURIComponent(_sj) + "&zeitraum=" + _zeitraum );
        },
		/**
		 * 
		 * @param {string} _sj Studienjahr z.B: '2013/14'
		 * @returns Promise
		 */
		importFOEBis: function() {
           return $http.get("../api.php?endpoint=importFOEBis");
		},
		/**
		 * UV
		 * 
		 * 
		 * @param {type} _sj
		 * @param {type} _version
		 * @returns {UV} Promise die bei Erfolg Datenstruktur mit aktualisierter
		 * UV Iteration liefert
		 */
		updateUvZeitraum: function(_sj,_zeitraum,_version) {
			return $http.get("../api.php?endpoint=refreshUV&studienjahr=" + encodeURIComponent(_sj) + "&zeitraum=" + _zeitraum + "&version=" + _version );
		},
        /**
         * UV holen
         * 
         * @param {string} studienjahr
         * @param {number} iterationNr Nummer der Iteration
         * @param {number} vorsemester Anzahl Vorsemester die angezeigt werden
         * sollen
         * @returns {UV} Datenstruktur mit UV Iteration
         */
		getUvFake: function(studienjahr, iterationNr, vorsemester) {
            // Daten aus uvFixture holen
            var iteration = null;
            for (var index = 0; index < iterationenFixture.length; ++index) {
                // Semester suchen
				if (uvFixture[index].studienjahr === studienjahr) {
                    var iterationen = uvFixture[index].iterationen;
                    iteration = iterationen[iterationNr - 1];
                    return iteration;
				}
			}	
            return null;
		},
		/**
         * Konvertiert die hierarchischen Daten in ein Array. Studiensemester
         * sind dabei untereinander.
         * 
         * @param {type} iteration
         * @returns {Array} Datenstruktur 
         */
        flattenDataNG: function(iteration) {
            var result = [];
            var gesamtDaten = iteration.gesamtDaten;
            
            // loop studiengaenge
            angular.forEach(gesamtDaten,function(stgRowValue,stgRowKey) {                

				
				//var studiengangDaten = value;						
				// Loop Semester
				angular.forEach(stgRowValue['studiengangDaten'], function(semesterRowValue, semesterRowKey) {
					
                    
                    
                    var flatRow = {};
					//var aktuellesSem = semesterRowValue.studiensemester;
					// flatRow[semesterRowKey] = semesterRowValue;  
					// Loop Daten
					angular.forEach(semesterRowValue, function(semValue,semKey) {
                        if (semKey !== 'gpzDiff' && semKey !== 'npzDiff') {
                            flatRow[semKey] = semValue;     
                        }
					});
                    // closures für Diff
                    flatRow.gpzDiff = function() {
                        return flatRow.gpzUv - flatRow.gpzBd;
                    };
                    flatRow.npzDiff = function() {
                        return flatRow.npzUv - flatRow.npzBd;
                    };
                    // Toleranz BD
                    flatRow.npzBdTfp = function() {
						if (flatRow.npzBdGesamt <= 100) {
							return (flatRow.npzBdGesamt * 0.1).toFixed(0);
						} else if (flatRow.npzBdGesamt <= 500) {
							return (10.0 + (flatRow.npzBdGesamt - 100) * 0.08).toFixed(0);
						} 
						return (42.0 + (flatRow.npzBdGesamt - 500) * 0.05).toFixed(0);
					};
					flatRow.npzBdTolUnten = function() {
						return flatRow.npzBdGesamt - flatRow.npzBdTfp();
					};
                    /*
                    flatRow.npzBdTfp = function() {
						if (flatRow.npzBdGesamt <= 100) {
							return (flatRow.npzBdGesamt * 0.1).toFixed(0);
						} else if (flatRow.npzBdGesamt <= 500) {
							return (10.0 + (flatRow.npzBdGesamt - 100) * 0.08).toFixed(0);
						} 
						return (42.0 + (flatRow.npzBdGesamt - 500) * 0.05).toFixed(0);
					};
					flatRow.npzBdTolUnten = function() {
						return flatRow.npzBdGesamt - flatRow.npzBdTfp();
					};*/

					 // clone studiengang daten
					angular.forEach(stgRowValue, function(value,key) {                                  
						if (key !== 'studiengangDaten') {      
							 flatRow[key] = value;  									
						}								
					});
                    
                    // Studienjahr
                    var jahr = parseInt(flatRow.studiensemester.substring(2,6));
                    if (flatRow.studiensemester.substring(0,2) === 'SS') jahr--;                                                    
                    flatRow.studienjahr = jahr + '/' + ((++jahr)-2000);
                    // Bewerber-Link
                    //flatRow.bewerber = UtilityService.generateBewerberLink(flatRow.stgKz,flatRow.studiensemester,flatRow.bewerber); 
                    // Interessenten-Link
                    //flatRow.interessenten = UtilityService.generateBewerberLink(flatRow.stgKz,flatRow.studiensemester,flatRow.interessenten); 
                    // dropout
                    //flatRow.dropout = UtilityService.generateBewerberLink(flatRow.stgKz,flatRow.studiensemester,flatRow.dropout); 
                    // studierende
                    //flatRow.studierende = UtilityService.generateBewerberLink(flatRow.stgKz,flatRow.studiensemester,flatRow.studierende); 
                    
                    //console.log(flatRow);
					result.push(flatRow);  

				});
              
                
            });
        
            return result;
        },
		
        /**
         * Konvertiert die hierarchischen Daten in ein Array sodass jqGrid
         * was damit anfangen kann.
         * 
         * @param {type} iteration
         * @returns {Array} Datenstruktur fuer das jqGrid
         */
        flattenData: function(iteration) {
            var result = [];
            var gesamtDaten = iteration.gesamtDaten;
            
            // loop studiengaenge
            angular.forEach(gesamtDaten,function(stgRow) {
                var flatRow = {};
                // loop studiengang daten
                angular.forEach(stgRow, function(value,key) {                                  
                    if (key === 'studiengangDaten') {                        
                        // Loop Semester
                        angular.forEach(value, function(semesterRow) {
                            var aktuellesSem = semesterRow.studiensemester;
                            // Loop Daten
                            angular.forEach(semesterRow, function(semValue,semKey) {
                                if (semKey !== 'studiensemester') {
                                    // semester and key dranhaengen
                                    flatRow[semKey + '_' + aktuellesSem] = semValue;
                                }
                            });
                        });
                    } else {
                        // studiengangdaten einfach kopieren (StgKz, OrgForm, ..)
                        flatRow[key] = value;  
                    } 
                });
                result.push(flatRow);
            });
        
            return result;
        },
        /**
         * Column Model fuer jqGrid erzeugen
         * @deprecated umgestiegen auf nggrid
         * @param {type} iteration
         * @param {string} studienjahr Studienjahr der UV Iteration
         * @param {type} anzahlVorsemester Anzahl der vergangenen Semester die
         * angezeigt werden sollen
         * @returns {Array} Datenstruktur fuer colModel Eigenschaft vom jqGrid
         */
        generateColumnModel: function(iteration, studienjahr, 
            anzahlVorsemester) {
            var columnModel;
            var basisColumns = [
                {name: 'stgKz', index: 'stgKz', width: 60, align: 'center', frozen: true},
                {name: 'stgBezeichnung', index: 'stgBezeichnung', width: 60, align: 'center', frozen: true},
                {name: 'stgArt', index: 'stgArt', width: 75, align: 'center', frozen: true},
                {name: 'orgForm', index: 'orgForm', width: 75, align: 'center', frozen: true}];
            var semesterColumns = [                
                {name: 'bewerber', index: 'bewerber', width: 75, formatter: 'number', sorttype: 'number', align: 'right', hidden: true, summaryType: 'sum'},
                {name: 'interessenten', index: 'interessenten', width: 100, formatter: 'number', sorttype: 'number', align: 'right', hidden: true, summaryType: 'sum'},
                {name: 'dropout', index: 'dropout', width: 75, formatter: 'number', sorttype: 'number', align: 'right', hidden: true, summaryType: 'sum'},
				{name: 'studierende', index: 'studierende', width: 75, formatter: 'number', sorttype: 'number', align: 'right', hidden: true, summaryType: 'sum'},
                {name: 'gpzBd', index: 'gpzBd', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'},
                {name: 'gpzUv', index: 'gpzUv', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, editable: true, summaryType: 'sum'},
                {name: 'gpzDiff', index: 'gpzDiff', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'},
                {name: 'tfpGpzBd', index: 'gpzBdTfp', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'},
                {name: 'tolUntenGpzBd', index: 'gpzBdTolUnten', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'},
               // {name: 'simGpzUv', index: 'simGpzUv', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, editable: true, summaryType: 'sum'},
               // {name: 'simGpzDiff', index: 'simGpzDiff', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'},
                {name: 'npzBd', index: 'npzBd', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'},
                {name: 'npzUv', index: 'npzUv', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, editable: true, summaryType: 'sum'},
                {name: 'npzDiff', index: 'npzDiff', width: 70, formatter: 'number', sorttype: 'number', align: 'right', sortable: true, summaryType: 'sum'}
            ];
            var columnModel = basisColumns;
            // Semester von Studienjahr holen studienjahr
            var aktuellesSemester = 'WS' + studienjahr.substr(0,4); 
            // string der an spaltennamen drangehaengt wird
            var suffix = UtilityService.addSemester(aktuellesSemester, -anzahlVorsemester);
            var vorsemesterCountdown = anzahlVorsemester;
            for(var i = 0; i < (anzahlVorsemester + iteration.zeitraum + 1); i++) {
                
                if (vorsemesterCountdown > 0) {
                    vorsemesterCounter--;
                }
                // clone erstellen und semester an spaltennamen dranhaengen
                angular.forEach(semesterColumns, function(colDef){                    
                    var tempSemesterColumn = {};
                    angular.forEach(colDef, function(value, key) {
                        // Name und Index
                        if (key === 'name' || key === 'index') {
                            tempSemesterColumn[key] = value + '_' + suffix;                        
                        // Vorsemester sind read only
                        } else if (vorsemesterCountdown > 0 
                            && key === 'editable') {
                            tempSemesterColumn[key] = false;
                        } else {
                            tempSemesterColumn[key] = value;
                        }
                       
                    });
                    columnModel.push(tempSemesterColumn);
                });  
                suffix = UtilityService.incSemester(suffix);
            }
            return columnModel;
        },
        /**
         * 
         * Erzeugt Array wie z.B.
         * <pre>
         * [
             {startColumnName: 'bewerber', numberOfColumns: 2, titleText: '<em>WS2013</em>'},
             {startColumnName: 'gpzBd', numberOfColumns: 2, titleText: '<em>SS2014</em>'}
           ]
           </pre>
         * 
		 * @deprecated umgestiegen auf nggrid
         * @param {type} iteration
         * @param {string} aktuellesSemester
         * @param {number} anzahlVorsemester
         * @returns {array} Datenstruktur fuer GroupHeaders von jqGrid
         */
        generateGroupHeaders: function(iteration, studienjahr, 
        anzahlVorsemester) {
            // Semester von Studienjahr holen studienjahr
            var aktuellesSemester = 'WS' + studienjahr.substr(0,4); 
            var result = [{
                    startColumnName: 'stgKz',
                    numberOfColumns: 4,
                    titleText: 'Studiengang'
                }];
            var tempSemester = UtilityService.addSemester(aktuellesSemester, -anzahlVorsemester);
            for(var i = 0; i < (anzahlVorsemester + iteration.zeitraum + 1); i++) {
                result.push({
                    startColumnName: 'bewerber_' + tempSemester,
                    numberOfColumns: 12,
                    titleText: '<em>' + tempSemester + '</em>'
                });
                tempSemester = UtilityService.incSemester(tempSemester);
            }
            
            return result;
        },
        /** 
         * Array fuer Spaltenueberschriften fuer jqGrid generieren
         * 
		 * @deprecated 
         * @param iteration Datenstruktur welche eine Iteration eines UV enthaelt
         * @param {number} vorsemester Anzahl der vergangenen Semester die
         * angezeigt werden sollen
         * @return {array} Spaltenueberschriften
         */
        generateColumnNames: function(iteration, vorsemester) {
            var basisNames = ['StgKz', 'Stg', 'StgArt', 'OrgForm'];
            var semesterNames = ['Bewerber', 'Interessenten', 'Drop-Out', 'Studierende', 
                'GPZ-BD', 'GPZ-UV', '&#916;GPZ', 'TFP GPZ', 'Tol unten GPZ', 
                /*'Sim GPZ-UV', 'Sim GPZ-Diff',*/ 'NPZ-BD', 'NPZ-UV', '&#916;NPZ'];
            var columnNames = basisNames;
            for(var i = 0; i < (vorsemester + iteration.zeitraum + 1); i++) {
                columnNames = columnNames.concat(semesterNames);
            }
            return columnNames;
        },
        /**
         * Nächste Nummer der Iteration in einem bestimmten Studienjahr holen
         * @param {string} sj Studienjahr im Format \d{4}\/d{2} (z.B: 2013/14)
         * @returns {number} iteration oder null wenn Studienjahr nicht vorhanden
         */
        getNextIteration: function(sj) {
            // iterationenFixture für Testzwecke
            // wird in Zukunft Server Request
            for (var i = iterationenFixture.length - 1; i >= 0; i--) {
               if (iterationenFixture[i].studienjahr === sj) {
                   return iterationenFixture[i].uvListe.length + 1;
               } 
               
            };
            return null;
             
        },
        /**
         *  Neues UV anlegen
         *  @param _sj Studienjahr
         *  @param _zeitraum Zeitraum in Studienjahren
         *  @return promiose von $http.get
         */
		create: function(_sj,_zeitraum) {
			return $http.get("../api.php?endpoint=newUV&studienjahr=" + encodeURIComponent(_sj) + "&zeitraum=" + _zeitraum);
		},
        /**
         *  Löschen des Umschichtungsvorhabens
         *  @param _sj Studienjahr
         *  @param _version Version (Nummer der Iteration)
         *  @return promise von $http.get
         */
		delete: function(_sj,_version) {
			return $http.get("../api.php?endpoint=deleteUV&studienjahr=" + encodeURIComponent(_sj) + "&version=" + _version);
		},
        /**
         *  Speichern des Umschichtungsvorhabens
         *  @param item Objekt das in Karteireiter gespeichert ist ($scope.uvTab)
         *  @return promise von $http.post
         */
        save: function(item, saveAs) {
            var anz = item.uvListe.length;
            var uv = [];
            for (var i = 0; i < anz; i++) {
                uv.push({stgKz:item.uvListe[i].stgKz,
                         stgArt:item.uvListe[i].stgArt,
                         orgForm:item.uvListe[i].orgForm,
                         studiensemester:item.uvListe[i].studiensemester,
                         npzUv:item.uvListe[i].npzUv,
                         gpzUv:item.uvListe[i].gpzUv});
            }
            var transferData = {
                studienjahr: item.bezeichnung,
                version: item.version,
                status: item.daten.status,
                zeitraum: item.daten.zeitraum,
                notizen:'',
                uvListe: uv,
                saveAs: saveAs
            };
            return $http.post("../api.php?endpoint=saveUV", transferData); 
        },
		saveSetup: function(bezeichnung,gridOptions) {
			var setupData = [];			
			var columns = gridOptions.$gridScope.columns;
			var anz = columns.length;
			for (var i = 0; i < anz; i++) {
				if (columns[i].field !== "") {
					setupData.push({
						field: columns[i].field,
						visible: (columns[i].visible !== undefined ? columns[i].visible : true),
						width: columns[i].width,
						sortDirection: columns[i].sortDirection,
						sortPriority: columns[i].sortPriority,
						isGroupedBy: columns[i].isGroupedBy,
                        groupIndex: columns[i].groupIndex,
                        index: columns[i].index
					});
				}
			}
			
			var transferData = {
                bezeichnung: bezeichnung,
                gridSetup: setupData
            };
            return $http.post("../api.php?endpoint=saveSetup", transferData);
		},
		deleteSetup: function(bezeichnung) {
            return $http.post("../api.php?endpoint=deleteSetup", {bezeichnung: bezeichnung});
		},
		getSetupList: function() {
			return $http.post("../api.php?endpoint=getSetupList");
		},
        getSetup: function(bezeichnung) {
			return $http.get("../api.php?endpoint=getSetup&bezeichnung=" + encodeURIComponent(bezeichnung));
				/*.then(function(data) {
					if (data.data.result == 1) {	
						return data.data.setupList;
					} else {
						alert('Fehler beim Holen der Setup-Daten: ' + data.data);
					}
			   }, function(reason) {				
					alert('Failed: ' + reason);				
			   });
			   return false;*/
			/*
            return [
					{field: 'stgBezeichnung',
					visible: true,
					width: 100,
					sortDirection: 'asc',
					sortPriority: undefined,
					isGroupedBy: true,
                    groupIndex: 0,
                    index: 1
                    },
					{field: 'stgKz',
					visible: true,
					width: 100,
					sortDirection: 'asc',
					sortPriority: undefined,
					isGroupedBy: true,
                    groupIndex: 1,
                    index: 2
                    }
                ];*/
        },
		
		
		// create funktion die nur auf fixture zugreift 
        createFake: function(_sj, _zeitraum) {
            var nextItNum = this.getNextIteration(_sj);
            if (nextItNum == null) nextItNum = 1;
            // Server Request zum Anlegen einer neuen UV aufrufen.
            // Dieser lliefert die neue UV als JSON
            // @todo
            // Testdaten updaten
            var uvMetadaten = {
                nr: nextItNum, lastupdate: '', notizen: '', status:'entwurf', zeitraum:_zeitraum
            };
            // iterationenFixture (Verzeichnis der vorhanden UVs)
            var iterationFound = false;
            angular.forEach(iterationenFixture, function(it) {
               if (it.studienjahr === _sj) {
                    it.uvListe.push(uvMetadaten);
                    iterationFound = true;
               } 
            });
            if (!iterationFound) {
                iterationenFixture.push({studienjahr:_sj,uvListe:uvMetadaten});
            }
            //
            for (var i = uvFixture.length - 1; i >= 0; i--) {
                 if (uvFixture[i].studienjahr === _sj) {
                     var nrNeu = uvFixture[i].iterationen.length + 1;
                     uvFixture[i].iterationen.push(
                        {nr: nrNeu, lastupdate:'',zeitraum:_zeitraum,status:'entwurf',
                             gesamtDaten:[
                                {"stgKz": "0227", "stgBezeichnung": "BDD", "stgArt": "Ba", "orgForm": "VZ", 
                                 studiengangDaten:generateFakeStudiengangDaten(_sj, _zeitraum)}]
                        }); 
                 }
             };
             return {
                 studienjahr:_sj,
                 item:uvMetadaten
             };
        },
		
	};
}).

service('UtilityService', function() {
    return {
        generateBewerberLink: function(stgKz, studiensemester, text) {
           return '<a href="https://vilesci.technikum-wien.at/content/statistik/bewerberstatistik.php?showdetails=true&studiengang_kz=' + stgKz + '&stsem=' + studiensemester + '" target="_blank">' + text + "</a>";   
        },
        
		/**
		 * Studienjahre für Chart-Beschriftung
		 * @param {string} sj Studienjahr
		 * @param {int} zeitraum
		 * @returns {Array} Semester beginnend mit SJ minus 5 Studienjahre und endend mit SJ plus Zeitraum Jahre
		 */
		generateStudiensemester: function(sj, zeitraum) {
			var jahr = 0;
            jahr = parseInt(sj.substr(0,4));			
			var letztesJahr = jahr + parseInt(zeitraum);
			var semesterList = [];
			var erstesJahr = jahr - 5;
			for(var j = erstesJahr; j < letztesJahr; j++) {
				semesterList.push('WS' + j);
				semesterList.push('SS' + (j + 1));
				
			}			
			return semesterList;
		},
		
		extractDaten: function(uvData, stgKz, stgArt, orgForm, attrName) {
			var result = [];            
            for (var index = 0; index < uvData.length; ++index) {
                //tempSJ = uvData[index].studienjahr;
                if (uvData[index].stgKz == stgKz && uvData[index].stgArt == stgArt
					&& uvData[index].orgForm == orgForm) {
                        if (typeof(uvData[index][attrName]) === 'function') {
                            result.push(uvData[index][attrName]());
                        } else {
                            result.push(uvData[index][attrName]);
                        }
                }   
            }
      
            return result;
		},
		
        extractStudienjahre: function(uvData) {
            var result = [];
            var tempSJ;
            for (var index = 0; index < uvData.length; ++index) {
                tempSJ = uvData[index].studienjahr;
                if (result.indexOf(tempSJ) == -1) {
                    result.push(tempSJ);
                }   
            }
      
            return result;
        },
        
		currentStudienjahr: function() {
			var currentDate = new Date();
			var month = currentDate.getMonth() + 1;
			var jahr = currentDate.getFullYear();
			if (month < 9) {
				jahr--;
			} 
			return jahr + "/" + ((jahr +1) + '').substr(2,2);
		},		
		
        incStudienjahr: function(sj) {
            var result = null;
            var jahr = 0;
            jahr = parseInt(sj.substr(0,4));
            jahr++;			
            result = jahr + '/' + ((jahr +1) + '').substr(2,2);
            return result;
        },
		
		decStudienjahr: function(sj) {
            var result = null;
            var jahr = 0;
            jahr = parseInt(sj.substr(0,4));
            jahr--;			
            result = jahr + '/' + ((jahr +1) + '').substr(2,2);
            return result;
        },
		
		subStudienjahr: function(sj,subtrahend) {
            var result = null;
            var jahr = 0;
            jahr = parseInt(sj.substr(0,4));
            jahr=jahr-subtrahend;			
            result = jahr + '/' + ((jahr +1) + '').substr(2,2);
            return result;
        },
        
        addSemester: function(semester, anzahl) {
            var result = semester;
            var anzahlAbs = (anzahl<0?anzahl*(-1):anzahl);
            for(var i = 0; i < anzahlAbs; i++) {
                if (anzahl < 0 ) {
                    result = this.decSemester(result);
                } else {
                    result = this.incSemester(result);
                }
            }
            return result;
        },
        /**
         * Semester um 1 erhoehen
         * 
         * @param {string} semester
         * @returns {string} semester
         */
        incSemester: function(semester) {
            var result = null;
            var jahr = 0;
            jahr = parseInt(semester.substr(2,4));
            if (semester.substr(0,2) === 'SS') {
				result = 'WS';
			} else {
				result = 'SS';
				jahr++;
			}
            result = result + jahr;
            return result;
        },
        /**
         * Semester um 1 erniedrigen 
         * @param {string} semester
         * @returns {string} semester
         */
        decSemester: function(semester) {
            var result = null;
            var jahr = 0;
            jahr = parseInt(semester.substr(2,4));
            if (semester.substr(0,2) === 'SS') {
				result = 'WS';
                jahr--;
			} else {
				result = 'SS';
			}
            result = result + jahr;
            return result;
        }
    }
})

.service('$fileUpload', ['$q','$http', function ($q,$http) {
    this.uploadFileToUrl = function(file, uploadUrl){
        var fd = new FormData();
		var deferred = $q.defer();		
        fd.append('file', file);
        $http.post(uploadUrl, fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        })
        .success(function(data, status, headers, config){			
			if (data.result === 0) {
				console.debug("Upload Fehler: " + data.errormsg);
				deferred.reject(data.errormsg);
			} else {
				console.debug("Upload erfolgreich ");
				deferred.resolve('Upload erfolgreich');
			}
        })
        .error(function(data, status, headers, config) {			
			console.debug("Fehler beim Upload [" + status + "]");	
			deferred.reject('Fehler beim Upload');
        });
		return deferred.promise;
    }
}]);

