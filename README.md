Symfony Project to parse a huge CSV file into MySQL.
================

The project parse a CSV file contains a large amount of URLs into MySQL table.

# How to Run
- You should have to install "xampp-windows-x64-7.4.29-1-VC15-installer" and start mysql server.

After the MySQL server is started, execute the "mydb.sql" file.

- You should have to load the project with VS Code(Visual Studio Code) and type as "symfony server:start" in Terminal.

After that, you should browse the web project in "localhost:8000" with Google Chrome.

# Requirement

The CSV file have to only contains URL array(splitted by "enter symbol").

# Resolution for your requirements

** I have done it by only one Sql Query "LOAD DATA" **

I have designed the table for url store as follows.
  ...
    `i_dns` varchar(253) NOT NULL,
	  `i_uri` text NOT NULL,
	  `i_parameter` text DEFAULT NULL,
  ...
  
  Set unique index (i_dns, i_uri, i_parameter)

  ex : URL - https://www.abcd.com/abc/cde/sde/wd?wd=43&sd=543&fd=23
       i_dsn = www.abcd.com
       i_uri = abc/cde/sde/wd
       i_parameter = wd=43&sd=543&fd=23

  1. Before loading CSV File into DB, you have to set options using mysqld commands as follows.
			                  mysqld --innodb_buffer_pool_size=4G,
                        mysqld --innodb_log_buffer_size=256M
                        mysqld --innodb_log_file_size=2G
                        mysqld --innodb_write_io_threads=48
                        mysqld --innodb_flush_log_at_trx_cmmit=0
	   After that, use "LOAD DATA" statement to load CSV File into DB. 
	   The duration time is a few seconds in case with 30000 URLs.
		  (see details in the code)
      (INFO : you could do that in other ways. you could modify the "my.ini" file with the options mentioned above and restart the mysql server manually.)

	2. In order to carry out your requirement, I have used Maria DB which is based on MySQL.
	   If you use Mysql DB, you can set data type of column with TEXT.
	   However, in order to set unique index with the TEXT column to avoid the duplicate, you have to set FULL TEXT index and using prefix.
	   But, in this case, you couldn't avoid dupicate perfectly.
	   
	   By the reasons mentioned above, I have used Maria DB.
	   In Maria DB, I can set data type of column with TEXT and set unique index with the TEXT column to satisfy your 3th requirements, store more than 2048 character.
		(see details in the Code)

	3. 1) I have ripped away the scheme from URL(http or https) string and don't insert the scheme data into DB.
		So, If the URLs differ only by the scheme, consider same while the "LOAD DATA" statement is executing.
	   2) I have done your requirement by a following part of "LOAD DATA" statement. (see details in PHP Code)
		(@var1) SET i_dns=IF (STRCMP(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(@var1, '://', -1), '/', 1), 			':', -1), '80')=0, SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(@var1, '://', -1), '/', 1), ':', 1), 			SUBSTRING_INDEX(SUBSTRING_INDEX(@var1, '://', -1), '/', 1))
	   3) Sorry. I haven't carried out this item.
		In order to do this item, I have to use JSON column which is only exist in MySQL DB.
		But, To satisfy your 2th, 3th - 1),2) requirements, I have used the Maria DB.

Thank you for giving me this important opportunity to help your company and improve my skills.
Best regards.
