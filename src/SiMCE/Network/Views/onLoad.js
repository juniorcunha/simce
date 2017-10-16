/**
 * 
 * Evento que é executado no SiMCE após
 * o carregamento do módulo.
 * 
 * @returns void
 */

SiMCE.onLoad = function() {

	setTimeout( function() {
		SiMCE.functions.createNetwork(); // Modo completo
	}, 500);

};
