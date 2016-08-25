# Tekserve

Some of projects I designed and developed in Tekserve

-----------------------------------------------------

**Apple Web Report Application**

Front-End : Angular JS + PHP + CSS3
Back-End : Factory, Singleton design patterns, LinkedList 

The goal is to build a web application which allows financial department to overview all Apple products sale performance in the last 4 quarters, including customers information, sales data, etc.

Regardless of the huge amount of dataset, the report should load and display results in a fairly fast speed

The report should contain filters like amount limit, division types, etc.

The report should contain search feature, so financial heads would be able to quickly find out information they need

There might be future addition to the report, please make sure the app is scalable, meanwhile the loading efficiency should keep sharp

-----------------------------------------------------

**FIFO Inventory Tracking App**

Front-End : Angular JS + jQuery + PHP + CSS3
Back-End : Singleton, Observer design patterns

This app is designed to track all in-stock items information

The app allows the company to find out total in-house value of all inventory items at the current time point 

Users can click to see the detailed information on any item, including:

location, number of in-house, total value, purchased value, number sold, manufacturer and other info

When customer made a return, FIFO app will push the returned item to the stack and find out the price from original purchase order

By end of every week, month and year, FIFO app will capture a snapshot and save to the database(MySQL), at the same time it will send out emails to company heads with reports attached

-----------------------------------------------------

**GSX Open Repair API**

The app is used to connect to Apple company's GSX API, track customers repair orders 

The data fetching took minutes, so I have set cache code to improve to less than 3 seconds

Angular JS is used to render the data for technicians

-----------------------------------------------------

**Shopkeeper Web Tracker**

A web portal used to record user's login statistics to each page/url, time, ip and browers info

The tool has graph showing top 10 users and webpages

Different options in the menu are used to check different section of statistics


