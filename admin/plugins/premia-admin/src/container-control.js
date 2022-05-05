document.addEventListener(
	'DOMContentLoaded',
	() => {
    let control = document.querySelector( '.premia-container-control' );
		if (control !== null) {
			control.addEventListener(
			'click',
			(e) => {
				e.preventDefault();
				if (e.target.classList.contains( 'button' )) {
					console.log( 'click' );
					console.log( e.target.dataset.id );
					console.log( e.target.dataset.action );
					fetch(
					wpApiSettings.root + 'premia/v1/container',
					{
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(
						{
							id: e.target.dataset.id,
							action: e.target.dataset.action
						}
					)
					}
					).then( response => response.json() )
					.then( data => console.log( data ) );
				}
			}
			)
		}
	}
)
