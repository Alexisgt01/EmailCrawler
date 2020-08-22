# Email Crawler

Retrieves all email addresses from a google search. On all result pages (1 to xx pages)

## Installation

``git clone https://github.com/Alexisgt01/EmailCrawler crawler``

``cd crawler && composer install``

## Utilization

``php search "site internet"``

You are limited to 2, maybe 3, usage per hour. Google only allows 50 requests per hour. (Error 429)

## Output

![Output](https://github.com/Alexisgt01/EmailCrawler/blob/master/output.png?raw=true)


The output is a .csv file present at the root of the directory
