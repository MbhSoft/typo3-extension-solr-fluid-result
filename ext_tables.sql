CREATE TABLE tx_solrfluidresult_domain_model_categoryfilteritem (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group varchar(100) DEFAULT '0' NOT NULL,
	parent_uid int(11) DEFAULT '0' NOT NULL,
	type tinytext,
	title tinytext,
	operator int(11) DEFAULT '0' NOT NULL,
	categories int(11) DEFAULT '0' NOT NULL,
	items int(11) DEFAULT '0' NOT NULL,
	parent int(11) DEFAULT '0' NOT NULL,
	query text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tt_content (
	tx_solrfluidresult_categoryfilteritem tinytext
);
