import sys
import csv
import MySQLdb
import MySQLdb.cursors
from optparse import OptionParser
from getpass import getpass


# allowed values for -f flag
allowed_types = ['users', 'courses', 'courseusers', 'items']


# export user info
def users():
    print "IN USERS"
    headers = ['Username', 'LastName', 'FirstName', 'LibraryID', 'Address1', 'Address2', 'Address3', 'City', 'State', 'Zip', 
               'Department', 'Status', 'EMailAddress', 'Phone1', 'Phone2', 'UserType', 'Password', 'PasswordHint', 
               'LastChangedDate', 'LastLoginDate', 'Cleared', 'ExpirationDate', 'Trusted', 'AuthMethod', 
               'CourseEmailDefault', 'ExternalUserId', 'RSSID', 
               'UserInfo1', 'UserInfo2', 'UserInfo3', 'UserInfo4', 'UserInfo5']


    # Only select Instructors
    # Leave CASE clause so logic can be changed more eaisly
    query = ''' SELECT username, user_id, first_name, last_name, email,
             CASE dflt_permission_level
                  WHEN 3 THEN 'Instructor'
             END AS usr_type
             FROM users
             WHERE dflt_permission_level = 3 AND username NOT LIKE '[tmp]%' '''

    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query)
    rows = cursor.fetchall()

    with open('users.csv', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL)
        writer.writeheader()
        for row in rows:
            csv_row = {'Username': row['username'], 'LastName': row['last_name'], 'FirstName': row['first_name'], 
                       'LibraryID': row['user_id'], 'EMailAddress': row['email'], 'UserType': row['usr_type'] }
            writer.writerow(csv_row)
            
            

# export course info
def courses():
    print "IN COURSES"
    headers = ['CourseID', 'Name', 'CourseCode', 'Description', 'URL', 'Semester', 
               'StartDate', 'StopDate', 'Department', 'Instructor', 'CourseNumber', 
               'CoursePassword', 'MaxCopyright', 'DefaultPickupSite', 'CourseEnrollment', 
               'ExternalCourseId', 'RegistrarCourseId'] 

    query = ''' SELECT ca.course_alias_id, c.uniform_title name, d.abbreviation course_code, ci.activation_date start_date, 
                    ci.expiration_date end_date,  c.course_number course_number, d.name department_name, CONCAT(u.first_name, u.last_name) instructor, ca.registrar_key registrar_key
                FROM courses c
                    JOIN departments d ON c.department_id = d.department_id
                    JOIN course_aliases ca ON ca.course_id = c.course_id
                    JOIN course_instances ci ON ci.primary_course_alias_id = ca.course_alias_id 
                    JOIN access a ON a.alias_id = ca.course_alias_id
                    JOIN users u ON u.user_id = a.user_id
                WHERE u.dflt_permission_level = 3 '''
             
            
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query)
    rows = cursor.fetchall()

    with open('courses.csv', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL)
        writer.writeheader()
        for row in rows:
            csv_row = {'CourseID': row['course_alias_id'], 'Name': row['name'], 'CourseCode': row['course_code'], 
                       'StartDate': row['start_date'], 'StopDate': row['end_date'], 'Department': row['department_name'], 
                       'Instructor': row['instructor'], 'CourseNumber': row['course_number'], 
                       'RegistrarCourseId': row['registrar_key']}
            writer.writerow(csv_row)

def course_user():
    print "IN COURSEUSERS"

    headers = ['CourseID', 'Username', 'UserType']

             
            

    query = ''' SELECT ca.course_alias_id, u.username username
                FROM courses c
                JOIN course_aliases ca ON ca.course_id = c.course_id
                JOIN course_instances ci ON ci.primary_course_alias_id = ca.course_alias_id
                JOIN access a ON a.alias_id = ca.course_alias_id
                JOIN users u ON u.user_id = a.user_id
WHERE u.dflt_permission_level = 3
AND u.username NOT LIKE '[tmp]%' '''
               
              
             
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query)
    rows = cursor.fetchall()

    with open('course_user.csv', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL)
        writer.writeheader()
        for row in rows:
            csv_row = {'CourseID': row['course_alias_id'], 'Username': row['username'], 'UserType': 'Instructor'}
            writer.writerow(csv_row)

def items():
    print "IN ITEMS"


if __name__=="__main__":
    #usage and options
    usage = '%prog [options]\n Export RD data to CSV format. Specify -f for each file format. Default is all. \n Allowed values are: \n users, courses, courseusers, items' 
    parser = OptionParser(usage)
    parser.add_option('--host', action='store', help='host of RD database')
    parser.add_option('--port', action='store', default=3306, help='port of RD database')
    parser.add_option('--username', action='store', help='username for RD database')
    parser.add_option('--password', action='store', help='password for RD database')
    parser.add_option('--db', action='store', help='RD database to which to connect')
    parser.add_option('-f', '--files', action='append', help='list of file formats to generate')
    (options, args) = parser.parse_args()


    #validate options
    if not options.host:
        parser.error('host must be specified')
    if not options.username:
        parser.error('username must be specified')
    if not options.db:
        parser.error('db must be specified')
    if options.password is None:
        parser.error('password must be specified. --password= will prompt for password')
    elif options.password=='':
        setattr(parser.values, 'password', getpass())

    #make sure all file types specified are vaalid
    if options.files:
        for f in options.files:
            if f not in allowed_types:
                parser.error("%s is not an allowed file type" % f)

    #connect to the Database
    try:  
        db = MySQLdb.connect(host=options.host, port=options.port, user=options.username, passwd=options.password, db=options.db)  
    except MySQLdb.Error, e:  
        print "Error %d: %s" % (e.args[0], e.args[1])  
        sys.exit (1) 

    #if not file types are specified run all else run only the ones specified
    if not options.files or 'users' in options.files:
        users()
    if not options.files or 'courses' in options.files:
        courses()
    if not options.files or 'courseusers' in options.files:
        course_user()
    if not options.files or 'items' in options.files:
        items()

    db.close()
