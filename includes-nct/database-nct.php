
				<?php
					define("ENVIRONMENT", "p");//d- development, p- production
					define("DB_HOST", "localhost");
					define("DB_USER", "root");
					define("DB_PASS", "");
					define("DB_NAME", "nlance_new");
					define("PROJECT_DIRECTORY_NAME", "nlance_web");
					define("SITE_URL", "http://localhost/nlance_web/");
					define("ADMIN_URL", "http://localhost/nlance_web/admin-nct/");
					define("DIR_URL", "/opt/lampp/htdocs/nlance_web/");
					define("D_KEY", "5c84348d4fac7b70a0df87b79fcb634f66443dfd21c23298565b400676a02b57");
					if(ENVIRONMENT == "d" ){
				        error_reporting(E_ALL | E_STRICT);

				    }else{
				        error_reporting(0);
				    }
				?>