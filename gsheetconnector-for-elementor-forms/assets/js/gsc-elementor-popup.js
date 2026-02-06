jQuery( document ).ready(
	function ($) {
		var $opener      = $( '#opener' );
		var $popup       = $( '#popup' );
		var $closeButton = $( '#closeButton' );
		var $popupOuter  = $( '#popup-outer' );

		$opener.on(
			'click',
			function () {
				fadeIn( $popup );
			}
		);

		$closeButton.on(
			'click',
			function () {
				fadeOut( $popup );
			}
		);

		$popupOuter.on(
			'click',
			function (event) {
				if (event.target === $popupOuter[0]) {
					fadeOut( $popup );
				}
			}
		);

		function fadeIn($element) {
			$element.css( { opacity: 0, display: 'block' } ).animate( { opacity: 1 }, 300 );
		}

		function fadeOut($element) {
			$element.animate(
				{ opacity: 0 },
				300,
				function () {
					$element.css( 'display', 'none' );
				}
			);
		}
	}
);
