import sys, git, os, re

def get_latest_tag(repo):
    tag = sorted(repo.tags, key=lambda t: t.commit.committed_datetime, reverse=True)
    if tag:
        return tag[0].name
    return '0.0.0'

def get_next_tag(tag):
    regex = re.match("^(.*?\.)(\d+)$", tag)
    if regex:
        tag = regex.groups()[0] + str(int(regex.groups()[1]) + 1)
    return tag

def push_new_tag(repo, new_tag_name):
    new_tag = repo.create_tag(new_tag_name, message='Automated Tag "{0}"'.format(new_tag_name))
    repo.remotes.origin.push(new_tag)

repo = git.Repo(os.getcwd())
tag = get_latest_tag(repo)
new_tag = get_next_tag(tag)
push_new_tag(repo, new_tag)