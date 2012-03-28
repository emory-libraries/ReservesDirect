import json
from xml.etree.ElementTree import XML
from fabric.api import env, local, prefix, put, sudo, task

# recommended distribution command:
#   $ fab load_config:fab_config.json deploy
#
# where settings.json is a path to a file that looks something like this:
#   {
#     "hosts": ["myself@remoteserver.library.emory.edu"], # where we'll ssh/scp
#     "sudo_user": "sudouser", # the user we'll sudo into to do stuff
#     "extract_path": "/path/to/sites/project" # where we'll extract the webapp
#   }
#
# if the new version doesn't work, you can revert symlinks with:
#   $ fab load_config:fab_config.json revert

# env targets

def _base_env():
    """Configure basic env."""
    env.version = None
    env.git_rev = None
    env.git_rev_tag = ''
    env.git_url = None
_base_env()

def _git_env():
    """Try to infer some env from local git checkout."""
    env.git_rev = local('git rev-parse --short HEAD', capture=True)
    env.git_branch = local('git symbolic-ref -q HEAD', capture=True)
    env.git_branch = env.git_branch.split('/')
    env.git_branch =  env.git_branch[-1]
    env.version =  local('cat VERSION', capture=True)

try:
    _git_env()

except:
    pass

def _env_paths():
    """Set some env paths based on previously-generated env."""
    env.build_dir = 'reserves-%(version)s-%(git_rev)s' % env
    env.tarball = 'reserves-%(version)s-%(git_rev)s.tar.bz2' % env
_env_paths()

@task
def load_config(file):
    """Load fab env variables from a local JSON file."""
    with open(file) as f:
        config = json.load(f)
    env.update(config)


# misc helpers

def _sudo(*args, **kwargs):
    """Wrapper for sudo, using a default user in env."""
    if 'user' not in kwargs and 'sudo_user' in env:
        kwargs = kwargs.copy()
        kwargs['user'] = env.sudo_user
    return sudo(*args, **kwargs)

# local build functions

def _fetch_source_from_git():
    """Fetch the source from git to avoid accidentally including local
    changes."""
    local('rm -rf  build dist')
    local('mkdir -p build')
    local('rm -rf build/%(build_dir)s' % env)
    local('git archive --output=build/%(build_dir)s.tar %(git_branch)s' % env)

def _package_source():
    """Create a tarball of the source tree."""
    local('mkdir -p dist')
    local('bzip2  build/%(build_dir)s.tar' % env)
    local('cp  build/%(tarball)s dist/%(tarball)s' % env)

# remote functions

def _copy_tarball():
    """Copy the source tarball to the target server."""
    put('dist/%(tarball)s' % env,
        '/tmp/%(tarball)s' % env)

def _extract_tarball():
    """Extract the remote source tarball in the appropriate directory."""
    _sudo('mv /tmp/%(tarball)s %(extract_path)s/%(tarball)s' % env)
    _sudo('mkdir -p  %(extract_path)s/%(build_dir)s' % env)
    _sudo('tar xjf %(extract_path)s/%(tarball)s -C %(extract_path)s/%(build_dir)s' % env)


def _remote_config():
    """Copy and configure config file setting."""
    _sudo('cp %(extract_path)s/%(build_dir)s/config_loc.inc.php.example %(extract_path)s/%(build_dir)s/config_loc.inc.php' % env)
    _sudo("sed -i 's`/path/to/config/file`%(extract_path)s/reserves_config.xml`g' %(extract_path)s/%(build_dir)s/config_loc.inc.php" % env)


def _update_links():
    """Update current/previous symlinks."""
    _sudo('''if [ -h %(extract_path)s/current ]; then
               rm -f %(extract_path)s/previous;
               mv %(extract_path)s/current %(extract_path)s/previous;
             fi''' % env)
    _sudo('ln -sf %(build_dir)s %(extract_path)s/current' % env)

# bring it all together

@task
def deploy():
    """Deploy the application from source control to a remote server."""    
    _fetch_source_from_git()
    _package_source()
    _copy_tarball()
    _extract_tarball()
    _remote_config()
    _update_links()

@task
def revert():
    """Back out the current version, updating remote symlinks to point back
    to the stored previous one."""
    _sudo('[ -h %(extract_path)s/previous ]' % env) # only if previous link exists
    _sudo('rm -f %(extract_path)s/current' % env)
    _sudo('ln -s $(readlink %(extract_path)s/previous) %(extract_path)s/current' % env)
    _sudo('rm %(extract_path)s/previous' % env)
    
@task
def clean_local():
    """Remove local files created during deployment."""
    local('rm -rf dist build')
    
@task
def get_latest_commit():
    """Run 'git update' to get the latest commit."""    
    try:
        local('git pull --rebase') 
        _svn_env()
        _env_paths()
    except:
        print "Try committing local changes first."
