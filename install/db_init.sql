/*
 * db_tables.php - SQL script to generate all the STEPS tables
 *
 * @author $Author:$
 * @version $Id:$
 * @copyright
*/

set foreign_key_checks=0;
drop table IF EXISTS access;
drop table IF EXISTS user_external;
drop table IF EXISTS career_category;
drop table IF EXISTS major_category;
drop table IF EXISTS resource_url;
drop table IF EXISTS goal;
drop table IF EXISTS user_role;
drop table IF EXISTS resource;
drop table IF EXISTS course_grade;
drop table IF EXISTS course_description;
drop table IF EXISTS users;
drop table IF EXISTS school;
drop table IF EXISTS auth;
drop table IF EXISTS department;
drop table IF EXISTS config;
set foreign_key_checks=1;

Create table access (
	id Int UNSIGNED NOT NULL,
	name Varchar(30),
 Primary Key (id)) ENGINE = MyISAM;


Create table config (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	system_version Varchar(30),
	system Varchar(50),
	UNIQUE (system),
 Primary Key (id)) ENGINE = MyISAM;

Create table department (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	enabled Tinyint DEFAULT 1,
	shortname Varchar(30),
	name Varchar(2000) NOT NULL,
	description Text,
 Primary Key (id)) ENGINE = MyISAM;

Create table auth (
	id Int UNSIGNED NOT NULL,
	type Varchar(30) NOT NULL,
 Primary Key (id)) ENGINE = MyISAM;

Create table school (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	shortname Varchar(20),
	name Varchar(1000),
 Primary Key (id)) ENGINE = MyISAM;

Create table users (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	auth_id Int UNSIGNED NOT NULL,
	student_id Int UNSIGNED,
	username Varchar(15) NOT NULL,
	enabled Bool DEFAULT 1,
	autogen Bool DEFAULT 1,
	lastlogin Datetime,
	lastfailedlogin Datetime,
	failedlogincount Int DEFAULT 0,
	password Varchar(32),
	email Varchar(15),
	first_name Varchar(20),
	middle_name Varchar(20),
	last_name Varchar(20),
	title Varchar(5),
	successips Varchar(20),
	failedips Varchar(20),
	UNIQUE (id),
	UNIQUE (username),
 Primary Key (id)) ENGINE = MyISAM;

Create table course_description (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	courseid Int UNSIGNED NOT NULL,
	lasteditorid Int UNSIGNED NOT NULL,
	creatorid Int UNSIGNED NOT NULL,
	createdate Datetime,
	modifydate Datetime,
	isedit Bool,
	prerequisites Text,
	coursedescription Text,
	resourcelist Text,
	summerreadings Text,
	# teachername Text,
	# subjectarea Varchar(200),
 Primary Key (id)) ENGINE = MyISAM;

Create table course_grade (
	courseid Int UNSIGNED NOT NULL,
	gradeid Int NOT NULL,
 Primary Key (courseid,gradeid)) ENGINE = MyISAM;

Create table resource (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	origid Int UNSIGNED,
	courseid Int UNSIGNED NOT NULL,
	unitid Int UNSIGNED NOT NULL,
	creatorid Int UNSIGNED NOT NULL,
	modifierid Int UNSIGNED,
	createdate Datetime,
	modifydate Datetime,
	type Int UNSIGNED NOT NULL,
	ispublic Bool DEFAULT 1,
	date1 Datetime,
	text1 Text,
 Primary Key (id)) ENGINE = InnoDB;

Create table upload (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	resourceid Int UNSIGNED NOT NULL,
	size Int UNSIGNED,
	filename Varchar(150) NOT NULL,
 Primary Key (id)) ENGINE = InnoDB;

Create table user_role (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	userid Int UNSIGNED NOT NULL,
	departmentid Int UNSIGNED,
	schoolid Int UNSIGNED,
	type Int UNSIGNED NOT NULL,
	grouping Varchar(30),
 Primary Key (id)) ENGINE = MyISAM;

Create table goal (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	creatorid Int UNSIGNED NOT NULL,
	status Tinyint UNSIGNED DEFAULT 1,
	createdate Datetime,
	modifydate Datetime,
	title Varchar(1024),
 Primary Key (id)) ENGINE = InnoDB;

Create table resource_url (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	resourceid Int UNSIGNED NOT NULL,
	url Varchar(500),
 Primary Key (id)) ENGINE = InnoDB;

Create table major_category (
	id Int NOT NULL AUTO_INCREMENT,
	major Varchar(100) NOT NULL,
	submajor Varchar(100) NOT NULL,
	majorname Varchar(100) NOT NULL,
        description Varchar(500) NOT NULL,
	sort Int DEFAULT 0,
 Primary Key (id)) ENGINE = MyISAM;

Create table career_category (
	id Int NOT NULL AUTO_INCREMENT,
        career Varchar(100) NOT NULL,
        subcareer Varchar(100) NOT NULL,
        careername Varchar(100) NOT NULL,
        description Varchar(500) NOT NULL,
	sort Int DEFAULT 0,
 Primary Key (id)) ENGINE = MyISAM;

Create table user_external (
	id Int UNSIGNED NOT NULL AUTO_INCREMENT,
	username Varchar(50) NOT NULL,
	failedlogincount Int DEFAULT 0,
	lastfailedlogin Datetime,
	lastlogin Datetime,
	successips Varchar(500),
	failedips Varchar(500),
	UNIQUE (username),
 Primary Key (id)) ENGINE = MyISAM;

Create Index ind_username ON user (username);

# The install or the release system will replace the app version number.

# Table user used to start with 100 and unit and resource start at 1
# Legacy installations use the old values
ALTER TABLE user AUTO_INCREMENT = 1001;

DROP TABLE IF EXISTS ci_sessions;

CREATE TABLE ci_sessions (
 id varchar(40) DEFAULT '0' NOT NULL,
 ip_address varchar(16) DEFAULT '0' NOT NULL,
 user_agent varchar(120) NOT NULL,
 last_activity int(10) unsigned DEFAULT 0 NOT NULL,
 data MEDIUMBLOB,
 timestamp DATE,
 PRIMARY KEY (id)
) ENGINE = MyISAM;

CREATE INDEX last_activity_idx ON ci_sessions(last_activity);

insert into auth (id, type) values (1, 'local');
insert into auth (id, type) values (2, 'ldap');

insert into access (id, name) values (1, 'admin');
#insert into access (id, name) values (2, 'admin_read');
#insert into access (id, name) values (3, 'editor');
insert into access (id, name) values (4, 'teacher');
insert into access (id, name) values (5, 'guest');
#insert into access (id, name) values (6, 'ict');


insert into user
(id, auth_id, accessid, enabled, username, password, firstname, lastname, displayname, autogen)
values
(2, 1, 5, 1, 'guest', 'NO_LOGIN', 'Guest', 'User', 'Guest User', 0);

insert into school (id,shortname,name) values
	(1,'ES', 'Elementary School'),
	(2,'MS', 'Middle School'),
	(3,'HS', 'High School');

insert into department (id, name) values
	(1,'English'),
	(2,'World Languages'),
	(3,'Mathematics'),
	(4,'Science'),
	(5,'Social Studies'),
	(6,'Arts'),
	(7,'Computer Science'),
	(8,'Physical Education');
