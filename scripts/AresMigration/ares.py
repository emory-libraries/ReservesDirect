import csv
from getpass import getpass
import MySQLdb
from optparse import OptionParser
import MySQLdb.cursors
import re
import sys


# allowed values for -f flag
allowed_types = ['users', 'courses', 'courseusers', 'items']

doc_types = { 'application/pdf': 'PDF',
                'audio/x-pn-realaudio': 'RealAudio',
                'video/quicktime': 'Quicktime Video',
                'application/msword': 'Microsoft Word',
                'application/vnd.ms-excel': 'Microsoft Excel',
                'application/vnd.ms-powerpoint': 'Microsoft Powerpoint',
                'text/html': 'WebLink',
                'image/jpeg': 'Image'
            }


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

# export course_user info
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

# export item data
def items():
    print "IN ITEMS"
    headers = ['ItemID', 'Username', 'CourseID', 'PickupLocation', 'ProcessLocation',
               'CurrentStatus', 'CurrentStatusDate', 'ItemType', 'DigitalItem', 'Location',
               'AresDocument', 'InstructorProvided', 'CopyrightRequired', 'CopyrightObtained', 'VisibleToStudents',
               'ActiveDate', 'InactiveDate', 'Callnumber', 'ReasonForCancellation', 'Proxy',
               'Title', 'Author', 'Publisher', 'PubPlace', 'PubDate',
               'Edition', 'ISXN', 'ESPNumber', 'CitedIn', 'DOI',
               'ArticleTitle', 'Volume', 'Issue', 'JournalYear', 'JournalMonth',
               'Pages', 'ShelfLocation', 'DocumentType', 'ItemFormat', 'Description',
               'CCCNumber', 'LoanPeriod', 'Editor', 'ReferenceNumber', 'ItemBarcode',
               'NeededBy', 'PagesEntireWork', 'PageCount', 'NatureOfWork', 'SortOrder',
               'ItemInfo1', 'ItemInfo2', 'ItemInfo3', 'ItemInfo4', 'ItemInfo5']


    query = ''' SELECT i.item_id, ca.course_alias_id, i.status, IF(i.item_group='ELECTRONIC', 1, 0) digital, i.url,
                       r.activation_date, r.expiration, i.title, i.author, i.publisher, i.volume_title, i.material_type,
                       r.requested_loan_period, i.pages_times_range, i.pages_times_total, i.pages_times_used, pc.call_number,
                       pc.barcode, i.local_control_key, OCLC, m.mimetype, i.volume_edition, l.reserve_desk, i.issn, i.isbn, i.source
                FROM reserves r
                    JOIN course_instances ci ON r.course_instance_id = ci.course_instance_id
                    JOIN course_aliases ca ON ci.primary_course_alias_id = ca.course_alias_id
                    JOIN items i ON i.item_id = r.item_id 
                    JOIN physical_copies pc ON pc.reserve_id = r.reserve_id  AND pc.item_id = i.item_id
                    JOIN mimetypes m ON i.mimetype = m.mimetype_id
                    JOIN libraries l ON i.home_library = l.library_id 
                WHERE i.item_group IN ('MONOGRAPH', 'MULTIMEDIA', 'ELECTRONIC')
                    AND i.status = 'ACTIVE'; ''' 
               
              
             
    year_p = re.compile('\d{4}') # pattern of year - used to search source field
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query)
    rows = cursor.fetchall()

    with open('items.csv', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers, quoting=csv.QUOTE_ALL)
        writer.writeheader()
        for row in rows:
            #grab digital value to do some specal logic with type and location
            digital = row['digital']
            if digital == 1:
                item_type = ''
                location = row['url']
            else:
                item_type='MON'
                location = ''

            #validate control key and skip item if it is not valid
            control_key = row['local_control_key'] 
            oclc = row['OCLC'] 
            if not control_key or control_key[:3] not in ['ocm', 'ocn'] or (not oclc or not control_key.endswith(oclc) ):
                print "WARNING: control_key: %s is not valid for item %s with OCLC: %s" % (control_key, row['item_id'], oclc)
                continue
             
            # get isbn or issn field based on material_type
            material_type = row['material_type']
            if material_type and 'BOOK' in material_type:
                isxn = row['isbn']
            elif material_type and material_type == 'JOURNAL_ARTICLE':
                isxn = row['issn']
            else:
                isxn = ''

            mime_type = row['mimetype']
            # AresDocument is true if pdf file
            ares_doc = 1 if mime_type.endswith('pdf') else 0

            # translate mimetype to DocumentType
            doc_type = doc_types.get(mime_type, '')

            # search source field to try to find a year
            source = row['source']
            result = re.search(year_p, source)
            if result:
                journal_year = result.group()
            else:
                journal_year = ''


            csv_row = {'ItemID': row['item_id'], 'CourseID': row['course_alias_id'], 'CurrentStatus': row['status'],
                       'ItemType': item_type, 'DigitalItem': digital, 'Location': location,  
                       'AresDocument': ares_doc, 'InstructorProvided': '1', 'CopyrightRequired': '1', 
                       'CopyrightObtained': '1', 'VisibleToStudents': '1', 
                       'ActiveDate': row['activation_date'], 'InactiveDate': row['expiration'], 'Proxy': '0',
                       'Title': row['title'], 'Author': row['author'], 'Publisher': row['publisher'],
                       'ArticleTitle': row['title'], 'Volume': row['volume_title'], 'ItemFormat': row['material_type'],
                       'LoanPeriod': row['requested_loan_period'], 'Pages': row['pages_times_range'], 
                       'PagesEntireWork': row['pages_times_total'], 'PageCount': row['pages_times_used'],
                       'Callnumber': row['call_number'], 'ItemBarcode': row['barcode'], 'ReferenceNumber': control_key,
                       'DocumentType': doc_type, 'Issue': row['volume_edition'], 'ShelfLocation': row['reserve_desk'],
                       'ISXN': isxn, 'JournalYear': journal_year
                       
                      }
            writer.writerow(csv_row)

if __name__=="__main__":
    #usage and options
    usage = '%prog [options]\n Export RD data to CSV format. Specify -f for each file format, Default is all formats. \n Allowed values for -f options are: \n users, courses, courseusers, items' 
    parser = OptionParser(usage)
    parser.add_option('--host', action='store', help='host of RD database')
    parser.add_option('--port', action='store', default=3306, help='port of RD database')
    parser.add_option('--username', action='store', help='username for RD database')
    parser.add_option('--password', action='store', help='password for RD database')
    parser.add_option('--db', action='store', help='RD database')
    parser.add_option('-f', '--file', action='append', help='list of file formats to generate. If none specified, all are generated')
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

    #make sure all file types specified are vaalid
    if options.file:
        for f in options.file:
            if f not in allowed_types:
                parser.error("%s is not an allowed file type" % f)

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
    if not options.file or 'items' in options.file:
        items()

    db.close()
