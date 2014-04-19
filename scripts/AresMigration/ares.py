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
allowed_types = ['users', 'courses', 'courseusers', 'items']

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

    with open('users.txt', 'wb') as f:
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
                    u.username, ca.registrar_key registrar_key,
                    l.ils_prefix default_pickup
                FROM courses c
                    JOIN departments d ON c.department_id = d.department_id
                    JOIN course_aliases ca ON ca.course_id = c.course_id
                    JOIN course_instances ci ON ci.primary_course_alias_id = ca.course_alias_id 
                    JOIN access a ON a.alias_id = ca.course_alias_id
                    JOIN users u ON u.user_id = a.user_id
                    JOIN reserves r ON r.course_instance_id = ci.course_instance_id
                    JOIN items i ON i.item_id = r.item_id 
                    LEFT JOIN physical_copies pc ON pc.item_id = i.item_id
                    LEFT JOIN mimetypes m ON i.mimetype = m.mimetype_id
                    JOIN libraries l ON d.library_id = l.library_id
                WHERE u.dflt_permission_level IN (3, 4, 5) 
                    AND username NOT LIKE '[tmp]%%' 
                    AND ((TRIM(u.first_name) != '' AND  u.first_name IS NOT NULL) OR (TRIM(u.last_name) != '' AND  u.last_name IS NOT NULL))
                    AND i.item_group IN ('MONOGRAPH', 'MULTIMEDIA', 'ELECTRONIC')
                    AND i.status = 'ACTIVE'
                    AND ci.activation_date >= %s '''
             
            
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query, options.date)
    rows = cursor.fetchall()

    with open('courses.txt', 'wb') as f:
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

            csv_row = {'CourseID': row['course_alias_id'], 'Name': row['name'], 'CourseCode': row['course_code'], 
                       'StartDate': row['start_date'], 'StopDate': row['end_date'], 'Department': row['department_name'], 
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

    with open('course_user.txt', 'wb') as f:
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

# export item data
def items():
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


    # get copyright and instructor notes
    copyright_notes = get_notes('copyright')
    instructor_notes = get_notes('instructor')

    # select items from specified date forward
    query = ''' SELECT DISTINCT i.item_id, ca.course_alias_id, i.status, IF(i.item_group='ELECTRONIC', 1, 0) digital, IFNULL(i.url, '') as url,
                       r.activation_date, r.expiration, i.title, i.author, i.publisher, i.volume_title, i.material_type,
                       r.requested_loan_period, i.pages_times_range, i.pages_times_total, i.pages_times_used, pc.call_number,
                       lpad(pc.barcode, 12, '0') barcode, i.local_control_key, OCLC, m.mimetype, IFNULL(i.volume_edition, '') volume_edition, l.reserve_desk, i.issn, i.isbn, i.source,
                       l.ils_prefix default_pickup
                FROM reserves r
                    JOIN course_instances ci ON r.course_instance_id = ci.course_instance_id
                    JOIN course_aliases ca ON ci.primary_course_alias_id = ca.course_alias_id
                    JOIN items i ON i.item_id = r.item_id 
                    LEFT JOIN physical_copies pc ON pc.item_id = i.item_id
                    LEFT JOIN mimetypes m ON i.mimetype = m.mimetype_id
                    LEFT JOIN libraries l ON i.home_library = l.library_id 
                WHERE i.item_group IN ('MONOGRAPH', 'MULTIMEDIA', 'ELECTRONIC')
                    AND i.status = 'ACTIVE' 
                    AND ci.activation_date >= %s '''
               
              
             
    year_p = re.compile('\d{4}') # pattern of year - used to search source field
    cursor = db.cursor (MySQLdb.cursors.DictCursor)
    cursor.execute (query, options.date)
    rows = cursor.fetchall()

    with open('items.txt', 'wb') as f:
        writer = csv.DictWriter(f, fieldnames=headers,  quoting=csv.QUOTE_ALL, delimiter='\t')
        writer.writeheader()
        print "ITEMS"
        pbar = ProgressBar(widgets=pbar_widget, maxval=len(rows)).start()
        for row in rows:
            # translate mimetype to DocumentType
            doc_type = doc_types.get(row['url'].split('.')[-1].lower(), '')
            if row['material_type'] in ['BOOK', 'DVD', 'VHS', 'CD']:
                doc_type = 'Hard Copy Reserve Item'

            #grab digital value to do some specal logic with type and location
            digital = row['digital']
            if digital == 1:
                item_type = 'SER'
                loan_period = ''
                location = row['url']
                if not location.startswith('http'):
                    location = location.replace('/', '_')
                else:
                    doc_type = "WebLink"
            else:
                item_type='MON'
                location = ''
                if doc_type == 'Hard Copy Reserve Item':
                    if default_pickup.get(row['default_pickup'], '') == 'OXFD':
                        loan_period = 2
                    else:
                        loan_period = 3
                else:
                    loan_period = ''

            # get isbn or issn field based on material_type
            material_type = row['material_type']
            if material_type and 'BOOK' in material_type:
                isxn = row['isbn']
            elif material_type and material_type == 'JOURNAL_ARTICLE':
                isxn = row['issn']
            else:
                isxn = ''

            # AresDocument is true if pdf file
            if location !=None:
                ares_doc = 0 if location.startswith('h') or location=='' else 1
            else:
                ares_doc = 0


            # search source field to try to find a year
            source = row['source']
            format = row['material_type']
            result = re.search(year_p, source) if source else ''
            if result:
                if format == 'JOURNAL_ARTICLE':
                    pub_year = ''
                    journal_year = result.group()
                else:
                    pub_year = result.group()
                    journal_year = ''
            else:
                pub_year = ''
                journal_year = ''


            # put lenght of portion info in pages unless it has a ':' which means it represents time. 
            # In that case put it in ItemInfo1
            range = row['pages_times_range']
            if range:
                if ':' in range:
                    pages = ''
                    info1= range
                else:
                    pages = range
                    info1= ''
            else:
                pages = ''
                info1= ''
   
            # if Title blank then use Article Title
            if not row['volume_title']:
                title = row['title'] 
                article_title = ''
            else:
                title = row['volume_title'] 
                article_title = row['title']

            try:
                volume, issue = row['volume_edition'].split(',')
            except:
                volume = row['volume_edition']
                issue = ''
            volume = volume.strip()
            issue = issue.strip()
                

            #Publisher and PubPlace
            pub = row['publisher'] if row['publisher'] else ''
            if ':' in pub:
                pub_place, publisher = pub.split(':', 1)
            else:
                publisher = pub
                pub_place= ''
            publisher = publisher.strip()
            pub_place = pub_place.strip()


            csv_row = {'ItemID': row['item_id'], 'CourseID': row['course_alias_id'], 'CurrentStatus': 'Item Removed From Reserves',
                       'ItemType': item_type[:50], 'DigitalItem': digital, 'Location': location,
                       'AresDocument': ares_doc, 'InstructorProvided': '1', 'CopyrightRequired': '1', 
                       'CopyrightObtained': '1', 'VisibleToStudents': '1', 
                       'ActiveDate': row['activation_date'], 'InactiveDate': row['expiration'], 'Proxy': '0',
                       'Author': row['author'], 'Publisher': publisher, 'PubPlace': pub_place,
                       'ArticleTitle': unnone(article_title)[:255], 'Title': unnone(title)[:255], 'ItemFormat': unnone(row['material_type'])[:50],
                       'Pages': pages[:30], 'PagesEntireWork': row['pages_times_total'], 'PageCount': row['pages_times_used'],
                       'Callnumber': unnone(row['call_number'])[:100], 'ItemBarcode': unnone(row['barcode'])[:50], 'ESPNumber': unnone(row['local_control_key'])[:32],
                       'DocumentType': unnone(doc_type)[:50], 'Volume': unnone(volume)[:30], 'Issue': unnone(issue)[:30], 'ISXN': unnone(isxn)[:20], 'PubDate': unnone(pub_year)[:30],
                       'JournalYear': journal_year[:30], 'ItemInfo1': info1[:255], 'ItemInfo2': copyright_notes.get(row['item_id'], '')[:255],
                       'PickupLocation': default_pickup.get(row['default_pickup'], 'THEO')[:10], 'ProcessLocation': default_pickup.get(row['default_pickup'], 'THEO')[:10],
                       'ItemInfo3': instructor_notes.get(row['item_id'], '')[:255], 'LoanPeriod': loan_period
            }
            writer.writerow(csv_row)
            records['items'] +=1
            pbar.update(records['items'])
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
    parser.add_option('-d', '--date', action='store', default='2011-08-01', help="Earliest activation date to query. Must use format 'yyyy-mm-dd'. (optional). default:'2011-08-01'")
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
    if not options.file or 'items' in options.file:
        items()

    db.close()


    print "Summary:"
    for file, count in records.iteritems():
        print "%s: %s"  % (file, count)
