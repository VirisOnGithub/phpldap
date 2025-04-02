<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="/datatables.min.css">
</head>
<body>
    <div class="container">
        <?php 
        function getOU(string $memberof): string {
            $exp = '/OU=([^,]*)/';
            $matches = [];
            preg_match($exp, $memberof, $matches);
            if(isset($matches[1])){
                return $matches[1];
            }
            return "";
        }

        error_reporting(E_ALL);
        if(isset($_POST["password"]) && isset($_POST['username'])){
            require_once('log_error.php');
            echo '<div>';
            set_time_limit(30);
            $user = $_POST['username'];
            $password = $_POST['password'];


            $ldapserver = 'ldap://WDC-1.WOODYWOOD.local';
            $ldapuser   = $user.'@WOODYWOOD';
            $ldappass   = $password;
            $ldaptree   = "OU=JURA,DC=WOODYWOOD,DC=local";

            $certpath   = "C:\\certificat.pem";
            // echo file_get_contents($certpath); => outputs the right content (php have the rights)

            ldap_set_option(NULL, LDAP_OPT_X_TLS_CACERTFILE, $certpath);
            ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_DEMAND);
            putenv('LDAPTLS_CACERT='.$certpath);

            // connect 
            $ldapconn = ldap_connect($ldapserver);

            if($ldapconn) {
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

                set_error_handler(function($en, $es, $ef, $el) {
                    if($en == E_WARNING){
                        log_error($_POST['username'], 'failure');
                        header('Location: connection.php?e=1');
                        exit();
                    }
                });
                $ldapbind = @ldap_bind($ldapconn, $ldapuser, $ldappass);
                restore_error_handler();
                if ($ldapbind) {                
                    $result = ldap_search($ldapconn,$ldaptree, "(cn=*)") or die ("Error in search query: ".ldap_error($ldapconn));
                    $data = ldap_get_entries($ldapconn, $result);
                    log_error($_POST['username'], 'success');

                    echo('<div class="table-container">');
                    echo '<h1>Users</h1>';
                    echo "<table id='adtable'>";
                    echo "<thead><th>Nom entier</th><th>Nom de famille</th><th>Prénom</th><th>OU</th></thead><tbody>"; 

                    for ($i = 0; $i < $data["count"]; $i++) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($data[$i]["cn"][0]) . "</td>";
                        echo "<td>" . htmlspecialchars(isset($data[$i]["sn"]) ? $data[$i]["sn"][0] : "") . "</td>";
                        echo "<td>" . htmlspecialchars(isset($data[$i]["givenname"]) ? $data[$i]["givenname"][0] : "") . "</td>";
                        echo "<td>" . htmlspecialchars(isset($data[$i]["memberof"]) ? getOU($data[$i]["memberof"][0]) : "") . "</td>";
                        
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                }
            }
            ldap_close($ldapconn);
            echo '</div>';

            // echo '<h1>Dump all data</h1><pre>';
            //     print_r($data);    
            // echo '</pre>';
        ?>
            <script src="/jquery.js"></script>
            <script src="/datatables.min.js"></script>
            <script>
                new DataTable('#adtable')
            </script>
        <?php } else { ?>
            <form class="login wrap" method="post">
                <div class="h1">Login</div>
                <input placeholder="Username" id="username" name="username" type="text">
                <input placeholder="Password" id="password" name="password" type="password">
                <?php if(isset($_GET['e'])){ ?>
                    <div class="error-message">Erreur : Le login ou le mot de passe est incorrect.</div>
                <?php } ?>
                <input value="Login" class="btn" type="submit">
            </form>
        <?php } ?>
    </div>
</body>
</html>