#coding:UTF-8

import requests
import threading
import queue
from peewee import *

queue = queue.Queue()

db = MySQLDatabase(host='localhost', user='root', database='song', charset='utf8')

class BaseModel(Model):
	class Meta:
		database = db

	# @classmethod
	# def getOne(cls, *query, **kwargs):
	# 	#为了方便使用，新增此接口，查询不到返回None，而不抛出异常
	# 	try:
	# 		return cls.get(*query,**kwargs)
	# 	except DoesNotExist:
	# 		return None

class Singers(BaseModel):
	artist = IntegerField()

rs = Singers.select().where(Singers.artist==0)
for r in rs:
	queue.put(str(r.id))

def run_thread():
	while not queue.empty():
		try:
			r = requests.get("http://dev/dev/public/song/get/" + queue.get())
			print(r.text)
		except:
			print('error')


threads=[]
for i in range(1,8):
	threads.append(threading.Thread(target=run_thread))

for t in threads:
	t.start()

for t in threads:
	t.join()