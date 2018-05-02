CHANGELOG for 0.2.*
===================

This changelog references the relevant changes (bug and security fixes) done
in all versions (major and minor)

To get the diff for a specific change, go to https://github.com/esatisfaction/esat-prestashop/commit/XXX where
XXX is the change hash

* 0.2.10 (2018-05-02)
  * [fix] Fixed passing false values for boolean config parameters
  * [fix] Fix multiple rows for each order in orders list
* 0.2.9 (2017-10-23)
  * [fix] Fix module key
* 0.2.8 (2017-09-21)
  * [fix] Check for result using is_array and isset when generating excel
* 0.2.2 (2017-03-27)
  * Make server api calls only if site id is set. Check for valid responses.
  * Porper sql query paraeters escape.
  * Ui changes to match prestashop defaults.
  * Update INSERT queries according to prestashop specifications.
  * Check for site id before executing order list code/page and order analysis code/page.
  * Update html tags and file structure based on prestashop standards/specifications.
* 0.2.1 (2017-03-02)
  * Fix email not passing to e-satisfaction upon checkout, resulting for most of the orders (but not all), resulting to customers not been able to receive the after sales questionnaire.
