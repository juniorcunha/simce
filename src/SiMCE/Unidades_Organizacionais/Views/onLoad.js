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
		{ mData: "nome",     sWidth: "200px" },
		{ mData: "endereco", sWidth: "200px" },
		{ mData: "cidade",   sWidth: "150px" },
		{ mData: "estado",   sWidth: "50px" },
		{ mData: "telefone", sWidth: "120px" }
	];
	cfg.sAjaxSource = SiMCE.moduleURL + "list/";
	$('#datagrid').dataTable(cfg);
	
};