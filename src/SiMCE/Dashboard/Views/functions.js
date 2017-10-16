/**
 * 
 * Bloco de funções do módulo.
 * 
 * @returns void
 */

SiMCE.functions = {
	
	/**
	 * Cria o gráfico de interceptações ativas
	 * 
	 * @return void
	 */
	createActiveInterceptionChart: function() {
		
		// Cria o gráfico com um array vazio
		var chart = AmCharts.makeChart("dashboard-statistics-active-calls", {
			"type": "serial",
			"theme": "none",
			"dataProvider": [],
			"colors": ["#84b761"],
			"valueAxes": [{
				"axisAlpha": 0.2,
				"dashLength": 1,
				"position": "left"
			}],
			"graphs": [{
				"id":"g1",
				"balloonText": "[[category]]<br /><span style='font-size:14px;'><b>Interceptações Ativas: </b>[[value]]</span>",
				"bullet": "round",
				"bulletBorderAlpha": 1,
				"bulletColor":"#FFFFFF",
				"hideBulletsCount": 50,
				"valueField": "value",
				"useLineColorForBulletBorder":true,
				"fillAlphas": 0.4,
				"type": "smoothedLine"
			}],
			"chartCursor": {
				"cursorPosition": "mouse"
			},
			"categoryField": "time",
			"categoryAxis": {
				//"parseDates": true,
				//"axisColor": "#DADADA",
				"dashLength": 1,
				"minorGridEnabled": true
			}
		});
		
		// Inicia a rotina de atualização do gráfico
		SiMCE.functions.updateActiveInterceptionChart( chart );
		
	},
	
	/**
	 * Adiciona dados ao gráfico de interceptações ativas
	 * 
	 * @return void
	 */
	updateActiveInterceptionChart: function( chart ) {
		
		// Verifica se existe ainda o painel
		if ($('#dashboard-statistics-active-calls').length == 0 || (!SiMCE.moduleURL.match(/dashboard/)))
			return;
		
		// Faz a requisição
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "active_interception/",
			{ operacao: SiMCE.getOperacao() },
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					
					// Verifica a quantidade de dados que existem no gráfico
					if (chart.dataProvider.length >= 20)
						chart.dataProvider.shift();
					
					// Adiciona o novo valor ao gráfico
					chart.dataProvider.push( data.content );

					// Valida o gráfico
					chart.validateData();
					
					// Agenda a execução para daqui a 1 segundo
					setTimeout( function() {
						SiMCE.functions.updateActiveInterceptionChart( chart );
					}, 1000 );
					
				}
			}
		);
		
	},
	
	/**
	 * Cria o gráfico de histórico de interceptações de áudio
	 * 
	 * @return void
	 */
	createCallHistoryChart: function() {
		
		// Faz a requisição
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "interception_history/",
			{ operacao: SiMCE.getOperacao(), tipo: 'A' },
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					
					var chart = AmCharts.makeChart("dashboard-statistics-call-history", {
						"type": "serial",
						"theme": "none",
						"dataProvider": data.content,
						"colors": ["#0066CC"],
						"valueAxes": [{
							"axisAlpha": 0.2,
							"dashLength": 1,
							"position": "left"
						}],
						"graphs": [{
							"id":"g1",
							"balloonText": "[[category]]<br /><span style='font-size:14px;'><b>Áudios: </b>[[value]]</span>",
							//"bullet": "round",
							//"bulletBorderAlpha": 1,
							//"bulletColor":"#FFFFFF",
							"hideBulletsCount": 50,
							"valueField": "value",
							"useLineColorForBulletBorder":true,
							"fillAlphas": 0.4,
							"type": "column"
						}],
						"chartCursor": {
							"cursorPosition": "mouse"
						},
						"categoryField": "hour",
						"categoryAxis": {
							//"parseDates": true,
							//"axisColor": "#DADADA",
							"dashLength": 1,
							"minorGridEnabled": true
						}
					});
					
				}
			}
		);
					
	},
	
	/**
	 * Cria o gráfico de histórico de interceptações de SMS
	 * 
	 * @return void
	 */
	createSMSHistoryChart: function() {
		
		// Faz a requisição
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "interception_history/",
			{ operacao: SiMCE.getOperacao(), tipo: 'G' },
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					
					var chart = AmCharts.makeChart("dashboard-statistics-sms-history", {
						"type": "serial",
						"theme": "none",
						"dataProvider": data.content,
						"colors": ["#FF6600"],
						"valueAxes": [{
							"axisAlpha": 0.2,
							"dashLength": 1,
							"position": "left"
						}],
						"graphs": [{
							"id":"g1",
							"balloonText": "[[category]]<br /><span style='font-size:14px;'><b>SMS: </b>[[value]]</span>",
							//"bullet": "round",
							//"bulletBorderAlpha": 1,
							//"bulletColor":"#FFFFFF",
							"hideBulletsCount": 50,
							"valueField": "value",
							"useLineColorForBulletBorder":true,
							"fillAlphas": 0.4,
							"type": "column"
						}],
						"chartCursor": {
							"cursorPosition": "mouse"
						},
						"categoryField": "hour",
						"categoryAxis": {
							//"parseDates": true,
							//"axisColor": "#DADADA",
							"dashLength": 1,
							"minorGridEnabled": true
						}
					});
					
				}
			}
		);
					
	},
	
	/**
	 * Cria o gráfico de histórico de interceptações de Dados
	 * 
	 * @return void
	 */
	createDataHistoryChart: function() {
		
		// Faz a requisição
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "interception_history/",
			{ operacao: SiMCE.getOperacao(), tipo: 'D' },
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					
					var chart = AmCharts.makeChart("dashboard-statistics-data-history", {
						"type": "serial",
						"theme": "none",
						"dataProvider": data.content,
						"colors": ["#663399"],
						"valueAxes": [{
							"axisAlpha": 0.2,
							"dashLength": 1,
							"position": "left"
						}],
						"graphs": [{
							"id":"g1",
							"balloonText": "[[category]]<br /><span style='font-size:14px;'><b>Dados: </b>[[value]]</span>",
							//"bullet": "round",
							//"bulletBorderAlpha": 1,
							//"bulletColor":"#FFFFFF",
							"hideBulletsCount": 50,
							"valueField": "value",
							"useLineColorForBulletBorder":true,
							"fillAlphas": 0.4,
							"type": "column"
						}],
						"chartCursor": {
							"cursorPosition": "mouse"
						},
						"categoryField": "hour",
						"categoryAxis": {
							//"parseDates": true,
							//"axisColor": "#DADADA",
							"dashLength": 1,
							"minorGridEnabled": true
						}
					});
					
				}
			}
		);
					
	},
	
	/**
	 * Cria os gráficos de utilização de CPU, Memória e Disco
	 * 
	 * @return void
	 */
	createPerformanceChart: function() {
		
		//=========================
		//
		// CPU
		//
		//=========================
		var cpuChart = AmCharts.makeChart("dashboard-general-cpu", {
			"type": "gauge",
			"axes": [{
				"axisThickness":1,
				 "axisAlpha":0.2,
				 "tickAlpha":0.2,
				"bands": [{
					"color": "#84b761",
					"endValue": 70,
					"startValue": 0
				}, {
					"color": "#fdd400",
					"endValue": 85,
					"startValue": 70
				}, {
					"color": "#cc4748",
					"endValue": 100,
					"startValue": 85
				}],
				"bottomText": "",
				"bottomTextYOffset": -20,
				"endValue": 100
			}],
			"arrows": [{}]
		});
		//gaugeChart.arrows[0].setValue(30);
		
		//=========================
		//
		// Memória
		//
		//=========================
		var memoryChart = AmCharts.makeChart("dashboard-general-memory", {
			"type": "gauge",
			"axes": [{
				"axisThickness":1,
				 "axisAlpha":0.2,
				 "tickAlpha":0.2,
				"bands": [{
					"color": "#84b761",
					"endValue": 50,
					"startValue": 0
				}, {
					"color": "#fdd400",
					"endValue": 70,
					"startValue": 50
				}, {
					"color": "#cc4748",
					"endValue": 100,
					"startValue": 70
				}],
				"bottomText": "",
				"bottomTextYOffset": -20,
				"endValue": 100
			}],
			"arrows": [{}]
		});
		//gaugeChart.arrows[0].setValue(75);
		
		//=========================
		//
		// Disco
		//
		//=========================
		var diskChart = AmCharts.makeChart("dashboard-general-disk", {
			"type": "serial",
			"theme": "none",
			"dataProvider": [],
			"valueAxes": [{
				"stackType": "regular",
				"axisAlpha": 0,
				"gridAlpha": 0,
				"visible":   false
			}],
			"graphs": [{
				"balloonText": "<span style='font-size:14px;'><b>[[title]]: </b>[[value]]%</span>",
				"fillAlphas": 0.8,
				"labelText": "[[value]]",
				"lineAlpha": 0.3,
				"title": "Utilizado",
				"type": "column",
				"color": "#000000",
				"valueField": "used"
			}, {
				"balloonText": "<span style='font-size:14px;'><b>[[title]]: </b>[[value]]%</span>",
				"fillAlphas": 0.8,
				"labelText": "[[value]]",
				"lineAlpha": 0.3,
				"title": "Disponível",
				"type": "column",
				"color": "#000000",
				"valueField": "free"
			}],
			"colors": ["#c64546","#84b761"],
			"rotate": true,
			"categoryField": "label",
			"categoryAxis": {
				"gridPosition": "start",
				"axisAlpha": 0,
				"gridAlpha": 0,
				"position": "left"
			}
		});
		
		// Inicia a rotina de atualização do gráfico
		SiMCE.functions.updatePerformanceChart( cpuChart, memoryChart, diskChart );
		
	},
	
	/**
	 * Atualiza os gráficos de performance
	 * 
	 * @return void
	 */
	updatePerformanceChart: function( cpu, memory, disk ) {	

		// Verifica se existe ainda o painel
		if ($('#dashboard-statistics-active-calls').length == 0 || (!SiMCE.moduleURL.match(/dashboard/)))
			return;
		
		// Faz a requisição
		SiMCE.ajaxRequest(
			"POST",
			SiMCE.moduleURL + "system_performance/",
			{},
			function( data, textStatus, jqXHR ) {
				if (data.success === true) {
					
					// CPU
					cpu.arrows[0].setValue( data.content.cpu );
					$("#dashboard-general-cpu-value").html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + data.content.cpu + "%");
					if ( data.content.cpu >= 85  )
						$("#dashboard-general-cpu-value").removeAttr("class").addClass("cus-bullet-red");
					else if ( data.content.cpu >= 70  )
						$("#dashboard-general-cpu-value").removeAttr("class").addClass("cus-bullet-yellow");
					else
						$("#dashboard-general-cpu-value").removeAttr("class").addClass("cus-bullet-green");
					
					// Swap
					memory.arrows[0].setValue( data.content.memoria );
					$("#dashboard-general-memory-value").html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + data.content.memoria + "%");
					if ( data.content.memoria >= 70  )
						$("#dashboard-general-memory-value").removeAttr("class").addClass("cus-bullet-red");
					else if ( data.content.memoria >= 50  )
						$("#dashboard-general-memory-value").removeAttr("class").addClass("cus-bullet-yellow");
					else
						$("#dashboard-general-memory-value").removeAttr("class").addClass("cus-bullet-green");
					
					// Disco
					disk.dataProvider = [{
						label: "",
						used:  data.content.disco,
						free:  100 - data.content.disco
					}];
					disk.validateData();
					$("#dashboard-general-disk-value-avail").html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + (100 - data.content.disco) + "%");
					$("#dashboard-general-disk-value-used").html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + data.content.disco + "%");
					
					// Agenda a execução para daqui a 1 minuto
					setTimeout( function() {
						SiMCE.functions.updatePerformanceChart( cpu, memory, disk );
					}, 60000 );
					
				}
			}
		);
		
	}
	
};