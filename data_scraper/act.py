#!/usr/bin/python
import requests
import base64
from urllib import quote
from yvih import models, db
from base import BaseData
from bs4 import BeautifulSoup
import csv


class ActData(BaseData):
    def __init__(self):
        super(ActData, self).__init__()
        self.list_url = 'http://www.parliament.act.gov.au/members/current'

    def actData(self):
        page = requests.get(self.list_url).content
        soup = BeautifulSoup(page)
        print soup.prettify()
        # table = soup.find('table', 'tablesorter')
        # print table