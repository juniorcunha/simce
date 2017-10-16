/**
 * 
 * Evento que é executado no SiMCE após
 * o carregamento do módulo.
 * 
 * @returns void
 */

SiMCE.onLoad = function() {
	
	// Cria o grid
	var cfg = SiMCE.getGridConfig(true);
	cfg.aoColumns = [
		{ mData: "nome",     sWidth: "33%" },
		{ mData: "login", sWidth: "33%" },
		{ mData: "email",   sWidth: "33%" }
	];
	cfg.sAjaxSource = SiMCE.moduleURL + "list/";
	$('#datagrid').dataTable(cfg);
	
};