/**
 * 
 * Evento que é executado no SiMCE após
 * o carregamento do módulo.
 * 
 * @returns void
 */

SiMCE.onLoad = function() {

	// Interceptações ativas
	SiMCE.functions.createActiveInterceptionChart();
	
	// Histórico de Ligações
	SiMCE.functions.createCallHistoryChart();
	
	// Histórico de SMS
	SiMCE.functions.createSMSHistoryChart();

	// Histórico de Dados
	SiMCE.functions.createDataHistoryChart();

	// Utilização de CPU, Memória e Disco
	SiMCE.functions.createPerformanceChart();
	
	// Cria o grid de usuários ativos
	// Cria o grid
	var cfg = SiMCE.getGridConfig(true);
	cfg.aoColumns = [
		{ mData: "nome",       sWidth: "33%" },
		{ mData: "last_ip",    sWidth: "33%" },
		{ mData: "last_login", sWidth: "33%" }
	];
	cfg.sAjaxSource = SiMCE.moduleURL + "list/";
	$('#datagrid').dataTable(cfg);

};