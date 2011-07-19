function FindProxyForURL(url, host) {
        if (isPlainHostName(host) ||
		dnsDomainIs(host, ".zipworld.com.au"))
                return "DIRECT" ;
	else if (isPlainHostName(host) ||
		dnsDomainIs(host, ".zipworld.net"))
		return "DIRECT" ;
        else if (isPlainHostName(host) ||
		dnsDomainIs(host, ".zip.com.au"))
                return "DIRECT" ;
        else
            return "PROXY adzapper.cs.zip.com.au:8081; PROXY proxy1.syd.zipworld.net:8080; PROXY proxy2.syd.zipworld.net:8080; PROXY proxy3.syd.zipworld.net:8080; DIRECT"
}
