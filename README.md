# openSIS-Classic
Communtiy Version 9.0 Release

openSIS is a commercial grade student information system aimed at security abnd scalability. Created by OS4ED, all functionalities that single or even multiple institutions need can be obtained in one installation.
With openSIS, it has never been easier to organize student information in relation to educational contexts in a K-12 environment.

#Features
- Manage Student Information
- View Courses
- Attendance and Grading
- Communication platform for faculty and students

#Required Software
openSIS Community Edition requires:
- Apache 2.4 or above
- PHP 8.x
- MySQL 5.7, 8.0, or Maria DB 10.4.x
Further details and links: https://github.com/OS4ED/openSIS-Classic/blob/master/docs/openSIS-CE%20Installation%20Guide.pdf

#Installation
Installing Apache:
https://www.apachefriends.org/download.html
- This will install Apache web server, MariaDB database, and PHP.
For installations on Ubuntu 22.04, please run:
1. sudo apt update
2. sudo apt install apache2
3. sudo ufw app list
4. sudo ufw allow in "Apache"
- Write: http://your-server-ip and this should display the default apache web page.
