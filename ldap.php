<?php
if (ldap_connect("ldaps://woodywood.local")) {
	echo "LDAP connection success!";
} else {
	echo "LDAP error";
}
?>