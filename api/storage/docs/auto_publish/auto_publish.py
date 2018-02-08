# -*- coding:utf-8 -*-  
import requests
import sys, json
import config
from pyquery import PyQuery as pq
reload(sys)
sys.setdefaultencoding( "utf-8" )

session = requests.Session()
session.trust_env = False

host = ""

# wiki登录
def wiki_login():
    wiki_login_url = "http://%s/dologin.action" % host
    user_data = config.user_data()
    login_response = session.post(wiki_login_url, data=user_data)

    login_content = unicode(login_response.content, 'utf-8')
    pq_login = pq(login_content)
    login_error = pq_login.find(".aui-message-error li").html()
    if (login_error):
        print login_error
        exit()

    cookie = login_response.headers.get('set-cookie')

    return cookie

# 打开本地文件
def open_file(file_name):
    file_object = open(file_name)
    try:
         all_the_text = file_object.read( )
    finally:
         file_object.close()

    return all_the_text


def edit_wiki(page_id, file_name):
    cookie = wiki_login()
    headers = {
        "Cookie": cookie
    }
    # 获取所需参数
    detail_url = "http://%s/rest/tinymce/1/content/%s.json?_=1489566702618" % (host, page_id)
    detail_response = session.get(detail_url, headers=headers)
    detail = json.loads(detail_response.content)

    edit_url = "http://%s/pages/doeditpage.action?pageId=%s" % (host, page_id)
    data = {
        'title' : detail['title'],
        'wysiwygContent' : open_file(file_name),
        'notifyWatchers':'true',
        'draftId':0,
        'originalVersion':int(detail['pageVersion']),
        'atl_token':detail['atlToken']
    }
    edit_response = session.post(edit_url, headers=headers, data=data)
    print "%s Updata Success" % file_name

if __name__ == '__main__':
    file_infos = config.file_to_update()
    for file_info in file_infos: 
        edit_wiki(file_info, file_infos[file_info])
