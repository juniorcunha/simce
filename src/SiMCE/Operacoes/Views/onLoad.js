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
		{ mData: "nome",     sWidth: "auto" },
		{ mData: "inicio",     sWidth: "150px" },
		{ mData: "fim",     sWidth: "150px" },
		{ mData: "vara",     sWidth: "150px" },
		{ mData: "autos",     sWidth: "150px" }
	];
	cfg.sAjaxSource = SiMCE.moduleURL + "list/";
	$('#datagrid').dataTable(cfg);
	
};