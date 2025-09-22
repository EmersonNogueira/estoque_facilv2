<?php
	namespace views;

	class View{
		

		const DEFAULT_HEADER = 'header.php';
		const DEFAULT_FOOTER = 'footer.php';

		public function render($body,$item= [],$header = null,$footer = null){
			extract($item);

			if($body=="login.php" || $body=="criarlogin.php" || $body==""){
				extract($item);

				include('views/templates/'.$body);

			}

			else{
				extract($item);
				if($header == null)
				{
					include('views/templates/'.self::DEFAULT_HEADER);
				}
				
				include('views/templates/'.$body);

				if($footer == null){
					include('views/templates/'.self::DEFAULT_FOOTER);
				}


			}


		}

	}
?>