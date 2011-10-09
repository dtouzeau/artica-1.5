var Constants = {};

/* Groups constants */
Constants.Group = {
	Type: { 
		ACCOUNT: 	0, 
		DOMAINS: 	1, 
		MAILBOXES: 	2,
		DOMAIN: 	3,
		MAILBOX: 	4
	}
};

/* Message constants */
Constants.Message = {
	Status: { 
		INSCANNER: 	1,
		FAILSAFE: 	2,
		QUARANTINE: 3,
		DELETED: 	4,
		DELIVERED: 	5
	},
	Verdict: { 
		VIRUS: 				1,
		VIRUSPROPABLE: 		2,
		SPAM: 				3,
		SPAMPROPABLE:		4,
		PHISHING: 			5,
		PHISHINGPROPABLE: 	6,
		CONTENT: 			7,
		NEGATIVETYPE: 		8,
		LARGESIZE: 			9,
		NEGATIVECONTENT: 	10,
		BLACKLIST: 			11
	}
};