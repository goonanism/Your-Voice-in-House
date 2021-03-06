#!/usr/bin/env python
# -*- coding: utf-8 -*-

import unittest
import os
import coverage
from data_scraper import base, act, federal, qld, nsw, nt, sa, vic, wa
from flask.ext.script import Manager
from yvih import app

manager = Manager(app)

app.config['DEBUG'] = True
app.config['PRESERVE_CONTEXT_ON_EXCEPTION'] = False


@manager.command
def test():
    """Runs the unit tests without coverage."""
    tests = unittest.TestLoader().discover('tests/', pattern='*.py')
    unittest.TextTestRunner(verbosity=2).run(tests)


@manager.command
def cov():
    """Runs the unit tests with coverage."""
    cov = coverage.coverage(
        branch=True,
        include='yvih/*'
    )
    cov.start()
    tests = unittest.TestLoader().discover('tests', pattern='*.py')
    unittest.TextTestRunner(verbosity=2).run(tests)
    cov.stop()
    cov.save()
    print('Coverage Summary:')
    cov.report()
    basedir = os.path.abspath(os.path.dirname(__file__))
    covdir = os.path.join(basedir, 'coverage')
    cov.html_report(directory=covdir)
    cov.erase()


@manager.command
def scrape_data():
    """Run data updates"""
    base.BaseData()
    federal_data = federal.FederalData()
    federal_data.generateSenate()
    federal_data.horCsvs()
    qld_data = qld.QldData()
    qld_data.qldData()
    act_data = act.ActData()
    act_data.actData()
    nsw_data = nsw.NswData()
    nsw_data.nswData()
    nt_data = nt.NtData()
    nt_data.ntData()
    sa_data = sa.SaData()
    sa_data.saData()
    vic_data = vic.VicData()
    vic_data.vicData()
    wa_data = wa.WaData()
    wa_data.waData()


@manager.command
def check_data():
    """Check for data changes. The idea being that this script can run on a
    cron to check if there have been any major changes to datasets."""
    federal_data = federal.FederalData()
    federal_data.updateSenate()

if __name__ == '__main__':
    manager.run()
