# -*- encoding: utf-8 -*-

# 0 - Student
# 1 - Custodian
# 2 - Proxy
# 3 - Instructor
# 4 - Staff
# 5 - Admin


from collections import defaultdict
import csv
from getpass import getpass
import MySQLdb
from optparse import OptionParser
import MySQLdb.cursors
import re
import sys
import time
from progressbar import ETA, Percentage, ProgressBar, Bar


# record counts
records = defaultdict(int)

# allowed values for -f flag
allowed_types = ['users', 'courses', 'courseusers']

doc_types = {
    'jpg' : 'Image-jpg',
    'jpeg' : 'Image-jpg',
    'xls' : 'Excel97-03',
    'xlsx' : 'Excel',
    'ppt' : 'Powerpoint97-03',
    'pptx' : 'Powerpoint',
    'doc' : 'Word97-03',
    'docx' : 'Word',
    'pdf' : 'PDF',
    'rtf' : 'RichText',
    'mp3' : 'MP3',
    'm4v' : 'Multimedia',
    'png' : 'Image-png',
    'rm' : 'Real Media-rm',
    'ram' : 'Real Media-rm',
    'html' : 'HTML',
    'htm' : 'HTML',
    'zip' : 'ZIP',
}


semester_codes={'0':'INTERIM', 
                '1':'SPRING', 
                '6':'SUMMER', 
                '9':'FALL'}

default_pickup = {'GEN' : 'MUSME',
                  'BUS' :'BUS',
                  'MM' : 'MUSME',
                  'HEALTH' : 'HLTH',
                  'OXF' :'OXFD',
                  'CHEM' : 'CHEM',
                  'THE' : 'THEO',
                  'LAW' : 'LAW'}

# widget for progress bar
pbar_widget = [Percentage(), ' ', ETA(),  Bar()]


def unnone(str):
    return str if str is not None else ''



# get notes by  type for later reference so it will not take 2 hours to run 82,000 seperate queries
def get_notes(type, sep='; '):

    query = ''' SELECT n.target_id id, IFNULL(group_concat(n.note separator %s), '') notes
                FROM notes n
                WHERE n.type = %s
                GROUP BY n.target_id '''

    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query, (sep, type))
    rows = cursor.fetchall()
    cursor.close()
    
    print "GETTING %s notes" % type
    notes = {}
    pbar = ProgressBar(widgets=pbar_widget, maxval=len(rows)).start()
    i=0
    for i, row in enumerate(rows):
        notes[row['id']] = row['notes']
        pbar.update(i)
    pbar.finish()

    return notes
    


 # export user info
def users():
    headers = ['Username', 'LastName', 'FirstName', 'LibraryID', 'Address1', 'Address2', 'Address3', 'City', 'State', 'Zip', 
               'Department', 'Status', 'EMailAddress', 'Phone1', 'Phone2', 'UserType', 'Password', 'PasswordHint', 
               'LastChangedDate', 'LastLoginDate', 'Cleared', 'ExpirationDate', 'Trusted', 'AuthMethod', 
               'CourseEmailDefault', 'ExternalUserId', 'RSSID', 
               'UserInfo1', 'UserInfo2', 'UserInfo3', 'UserInfo4', 'UserInfo5']


    # Only select Instructors, Staff, Admin
    # Leave CASE clause so logic can be changed more eaisly
    query = ''' SELECT username, user_id, first_name, last_name, email,
             CASE dflt_permission_level
                  WHEN 3 THEN 'Instructor'
                  WHEN 4 THEN 'Staff'
                  WHEN 5 THEN 'Admin'
             END AS usr_type
             FROM users
             WHERE dflt_permission_level IN (3, 4, 5) 
                 AND ((TRIM(first_name) != '' AND  first_name IS NOT NULL) OR (TRIM(last_name) != '' AND  last_name IS NOT NULL))
                 AND username NOT LIKE '[tmp]%' ''' 

    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query)
    rows = cursor.fetchall()

    with open('usersAll.txt', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL, delimiter='\t')
        writer.writeheader()
       
        print "USERS"
        pbar = ProgressBar(widgets=pbar_widget, maxval=len(rows)).start()
        for row in rows:
            csv_row = {'Username': row['username'], 'LastName': row['last_name'], 'FirstName': row['first_name'], 
                       'LibraryID': row['username'], 'EMailAddress': row['email'], 'UserType': row['usr_type'], 'Cleared':'Yes', 
                       'Trusted': 1, 'Status': row['usr_type']
            }
            writer.writerow(csv_row)
            records['users'] +=1           
            pbar.update(records['users'])
        pbar.finish()

# export course info
def courses():
    headers = ['CourseID', 'Name', 'CourseCode', 'Description', 'URL', 'Semester', 
               'StartDate', 'StopDate', 'Department', 'Instructor', 'CourseNumber', 
               'CoursePassword', 'MaxCopyright', 'DefaultPickupSite', 'CourseEnrollment', 
               'ExternalCourseId', 'RegistrarCourseId'] 

    # only instructors, Staff, Admin courses from date specified forward
    query = ''' SELECT DISTINCT ca.course_alias_id, c.uniform_title name, d.abbreviation course_code, ci.activation_date start_date, 
                    ci.expiration_date end_date,  c.course_number course_number, d.name department_name, d.abbreviation,
                    ca.registrar_key registrar_key,
                    l.ils_prefix default_pickup, ca.section
                FROM courses c
                    JOIN departments d ON c.department_id = d.department_id
                    JOIN course_aliases ca ON ca.course_id = c.course_id
                    JOIN course_instances ci ON ci.primary_course_alias_id = ca.course_alias_id
                    JOIN access a ON a.alias_id = ca.course_alias_id
                    JOIN libraries l ON d.library_id = l.library_id
                WHERE ci.activation_date >=  %s '''
             
            
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query, options.date)
    rows = cursor.fetchall()

    with open('coursesAll.txt', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL, delimiter='\t')
        writer.writeheader()

        print "COURSES"
        pbar = ProgressBar(widgets=pbar_widget, maxval=len(rows)).start()
        for row in rows:

            #decode register key into semester info
            registrar_key = row['registrar_key']
            # assume key starts with 5 which is 21st century which is year 20XX then add last two digits of year.
            if registrar_key:
                semester = "20%s" % registrar_key[1:3] 
                semester = "%s %s" % (semester_codes[registrar_key[3:4]], semester)
            else:
                semester = ''

            course_number = "%s %s" % (row['abbreviation'], row['course_number'])

            # add additional lookup for default_pickup
            default_pickup[row['course_alias_id']] = default_pickup.get(row['default_pickup'], '')

            csv_row = {'CourseID': row['course_alias_id'], 'Name': row['name'], 'CourseCode': row['section'],
                       'StartDate': row['start_date'], 'StopDate': row['end_date'], 'Department': row['abbreviation'],
                       'Instructor': '', 'CourseNumber': course_number,
                       'RegistrarCourseId': registrar_key, 'Semester': semester, 'DefaultPickupSite': default_pickup.get(row['default_pickup'], ''),
                       'ExternalCourseId': row['course_alias_id'] 
            }
            writer.writerow(csv_row)
            records['courses'] +=1 
            pbar.update(records['courses'])
        pbar.finish()
        cursor.close()

# export course_user info
def course_user():

    headers = ['CourseID', 'Username', 'UserType']


    # select course_userss for Instructors, Staff, Admin from specified date forward
    query = ''' SELECT DISTINCT ca.course_alias_id, u.username username,
                    CASE u.dflt_permission_level
                      WHEN 3 THEN 'Instructor'
                      WHEN 4 THEN 'Staff'
                      WHEN 5 THEN 'Admin'
                    END AS usr_type
                FROM courses c
                    JOIN course_aliases ca ON ca.course_id = c.course_id
                    JOIN course_instances ci ON ci.primary_course_alias_id = ca.course_alias_id
                    JOIN access a ON a.alias_id = ca.course_alias_id
                    JOIN users u ON u.user_id = a.user_id
                    JOIN reserves r ON r.course_instance_id = ci.course_instance_id
                    JOIN items i ON i.item_id = r.item_id 
                    LEFT JOIN physical_copies pc ON pc.item_id = i.item_id
                    LEFT JOIN mimetypes m ON i.mimetype = m.mimetype_id
                    LEFT JOIN libraries l ON i.home_library = l.library_id 
                WHERE u.dflt_permission_level IN (3, 4, 5)
                    AND ((TRIM(u.first_name) != '' AND  u.first_name IS NOT NULL) OR (TRIM(u.last_name) != '' AND  u.last_name IS NOT NULL))
                    AND u.username NOT LIKE '[tmp]%%'  
                    AND i.item_group IN ('MONOGRAPH', 'MULTIMEDIA', 'ELECTRONIC')
                    AND i.status = 'ACTIVE'
                    AND ci.activation_date >= %s '''
               
              
             
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query, (options.date))
    rows = cursor.fetchall()

    with open('course_userAll.txt', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL, delimiter='\t')
        writer.writeheader()
        print "COURSE_USER"
        pbar = ProgressBar(widgets=pbar_widget, maxval=len(rows)).start()
        for row in rows:
            csv_row = {'CourseID': row['course_alias_id'], 'Username': row['username'], 'UserType': row['usr_type']}
            writer.writerow(csv_row)
            records['course_user'] +=1
            pbar.update(records['course_user'])
        pbar.finish()
        cursor.close()

if __name__=="__main__":
    #usage and options
    usage = '%prog [options]\n Export RD data to CSV format. Specify -f for each file format, Default is all formats. \n Allowed values for -f options are: ' + ', '.join(allowed_types) 
    parser = OptionParser(usage)
    parser.add_option('--host', action='store', help='host of RD database (required)')
    parser.add_option('--port', action='store', default=3306, help='port of RD database (optional)')
    parser.add_option('--username', action='store', help='username for RD database (required)')
    parser.add_option('--password', action='store', help='password for RD database (required) --password= will prompt for password')
    parser.add_option('--db', action='store', help='RD database (required)')
    parser.add_option('-f', '--file', action='append', help='list of file formats to generate (optional). If none specified, all formats are generated')
    parser.add_option('-d', '--date', action='store', default='2014-05-01', help="Earliest activation date to query. Must use format 'yyyy-mm-dd'. (optional). default:'2011-08-01'")
    (options, args) = parser.parse_args()


    #validate options
    if not options.host:
        parser.error('host must be specified')
    if not options.username:
        parser.error('username must be specified')
    if not options.db:
        parser.error('db must be specified')
    # accept password on command line or prompt if password is empty
    if options.password is None:
        parser.error('password must be specified. --password= will prompt for password')
    elif options.password=='':
        setattr(parser.values, 'password', getpass())

    # make sure all file types specified are vaalid
    if options.file:
        for f in options.file:
            if f not in allowed_types:
                parser.error("%s is not an allowed file type" % f)
    # validate date if entered
    if options.date:
        try:
            time.strptime(options.date, '%Y-%m-%d')
        except:
            parser.error("date entered is invalid or in the wrong format use ('yyyy-mm-dd')")

    # print settings specified
    print "Connecting to database %s %s@%s:%s" % (options.db, options.username, options.host, options.port)
    print "Using activation_date %s" % (options.date)
    if options.file:
        print "Generating file(s) %s" % (', '.join(options.file))
    else:
        print "Generating file(s) %s" % (', '.join(allowed_types))


    #connect to the Database
    try:  
        db = MySQLdb.connect(host=options.host, port=options.port, user=options.username, passwd=options.password, db=options.db)  
    except MySQLdb.Error, e:  
        print "Error %d: %s" % (e.args[0], e.args[1])  
        sys.exit (1) 

    #if no file types are specified run all else run only the ones specified
    if not options.file or 'users' in options.file:
        users()
    if not options.file or 'courses' in options.file:
        courses()
    if not options.file or 'courseusers' in options.file:
        course_user()

    db.close()


    print "Summary:"
    for file, count in records.iteritems():
        print "%s: %s"  % (file, count)
