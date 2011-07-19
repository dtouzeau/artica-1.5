function FindProxyForURL(url, host) {

	if (isPlainHostName(host)
	 || shExpMatch(host, "*.home")
	   )
                return "DIRECT" ;
        else
            return "PROXY proxy:8080; PROXY proxy-optus:8080; DIRECT";

}
