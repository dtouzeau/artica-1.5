<?php












function Generate_opensslconf($path){
	
	$cert[]="HOME			= .";
	$cert[]="RANDFILE		= \$ENV::HOME/.rnd";
	$cert[]="oid_section		= new_oids";
	$cert[]="[ new_oids ]";
	$cert[]="[ ca ]";
	$cert[]="default_ca	= CA_default";
	$cert[]="[ CA_default ]";
	$cert[]="";
	$cert[]="dir		= /etc/ipsec.d";
	$cert[]="certs		= \$dir/certs";
	$cert[]="crl_dir		= \$dir/crl";
	$cert[]="database	= \$dir/index.txt";
	$cert[]="new_certs_dir	= \$dir/newcerts";
	$cert[]="certificate	= \$dir/private/cacert.pem";
	$cert[]="serial		= \$dir/serial";
	$cert[]="crlnumber	= \$dir/crlnumber";
	$cert[]="crl		= \$dir/crl.pem";
	$cert[]="private_key	= \$dir/private/cakey.pem";
	$cert[]="RANDFILE	= \$dir/private/.rand";
	$cert[]="x509_extensions	= usr_cert";
	$cert[]="subjectAltName 	= DNS:plm61.in.itm.com";
	$cert[]="extendedKeyUsage = serverAuth";
	$cert[]="name_opt 	= ca_default";
	$cert[]="cert_opt 	= ca_default";
	$cert[]="default_days	= 365";
	$cert[]="default_crl_days= 30";
	$cert[]="default_md	= sha1";
	$cert[]="preserve	= no";
	$cert[]="policy		= policy_match";
	$cert[]="[ policy_match ]";
	$cert[]="countryName		= match";
	$cert[]="stateOrProvinceName	= match";
	$cert[]="organizationName	= match";
	$cert[]="organizationalUnitName	= optional";
	$cert[]="commonName		= supplied";
	$cert[]="emailAddress		= optional";
	$cert[]="";
	$cert[]="[ policy_anything ]";
	$cert[]="countryName		= optional";
	$cert[]="stateOrProvinceName	= optional";
	$cert[]="localityName		= optional";
	$cert[]="organizationName	= optional";
	$cert[]="organizationalUnitName	= optional";
	$cert[]="commonName		= supplied";
	$cert[]="emailAddress		= optional";
	$cert[]="[ req ]";
	$cert[]="default_bits		= 1024";
	$cert[]="default_keyfile 	= privkey.pem";
	$cert[]="distinguished_name	= req_distinguished_name";
	$cert[]="attributes		= req_attributes";
	$cert[]="x509_extensions	= v3_ca	# The extentions to add to the self signed cert";
	$cert[]="";
	$cert[]="[ req_distinguished_name ]";
	$cert[]="countryName			= Country Name (2 letter code)";
	$cert[]="countryName_default		= AU";
	$cert[]="countryName_min			= 2";
	$cert[]="countryName_max			= 2";
	$cert[]="stateOrProvinceName		= State or Province Name (full name)";
	$cert[]="stateOrProvinceName_default	= Some-State";
	$cert[]="localityName			= Locality Name (eg, city)";
	$cert[]="0.organizationName		= Organization Name (eg, company)";
	$cert[]="0.organizationName_default	= Internet Widgits Pty Ltd";
	$cert[]="organizationalUnitName		= Organizational Unit Name (eg, section)";
	$cert[]="commonName			= Common Name (eg, YOUR name)";
	$cert[]="commonName_max			= 64";
	$cert[]="emailAddress			= Email Address";
	$cert[]="emailAddress_max		= 64";
	$cert[]="";
	$cert[]="[ req_attributes ]";
	$cert[]="challengePassword		= A challenge password";
	$cert[]="challengePassword_min		= 4";
	$cert[]="challengePassword_max		= 20";
	$cert[]="unstructuredName		= An optional company name";
	$cert[]="";
	$cert[]="[ usr_cert ]";
	$cert[]="";
	$cert[]="basicConstraints=CA:FALSE";
	$cert[]="nsComment			= \"OpenSSL Generated Certificate\"";
	$cert[]="subjectKeyIdentifier=hash";
	$cert[]="authorityKeyIdentifier=keyid,issuer";
	$cert[]="";
	$cert[]="[ v3_req ]";
	$cert[]="basicConstraints = CA:FALSE";
	$cert[]="keyUsage = nonRepudiation, digitalSignature, keyEncipherment";
	$cert[]="";
	$cert[]="[ v3_ca ]";
	$cert[]="";
	$cert[]="subjectKeyIdentifier=hash";
	$cert[]="authorityKeyIdentifier=keyid:always,issuer:always";
	$cert[]="basicConstraints = CA:true";
	$cert[]="[ crl_ext ]";
	$cert[]="authorityKeyIdentifier=keyid:always,issuer:always";
	$cert[]="";
	$cert[]="[ proxy_cert_ext ]";
	$cert[]="basicConstraints=CA:FALSE";
	$cert[]="nsComment			= \"OpenSSL Generated Certificate\"";
	$cert[]="subjectKeyIdentifier=hash";
	$cert[]="authorityKeyIdentifier=keyid,issuer:always";
	$cert[]="proxyCertInfo=critical,language:id-ppl-anyLanguage,pathlen:3,policy:foo";
	$cert[]="";	
}