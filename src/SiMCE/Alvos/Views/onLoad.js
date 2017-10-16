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
		{ mData: "nome",    sWidth: "30%" },
		{ mData: "apelido", sWidth: "30%" },
		{ mData: "rg",      sWidth: "20%" },
		{ mData: "cpf",     sWidth: "20%" }
	];
	cfg.sAjaxSource = SiMCE.moduleURL + "list/?operacao=" + SiMCE.getOperacao();
	$('#datagrid').dataTable(cfg);
	
};