<?php

// ==================================================================
//
// The Default DB Config file works for Travis-CI
//
// Setup your own DB Config file 'my.db.config.php' to work with
// your own environment
//
// ------------------------------------------------------------------

interface MySQL {

  const HOST = "127.0.0.1"; // MySQL Host
  const DB   = "beanbasetest";      // MySQL DB Name
  const USER = "root";      // MySQL User name
  const PASS = "";          // MySQL Password

}

interface PgSQL {

  const HOST = "127.0.0.1"; // Postgres Host
  const DB   = "beanbasetest";      // Postgres DB Name
  const USER = "postgres";  // Postgres User name
  const PASS = "";          // Postgres Password

}

interface SQLite {

  const FILE = "/tmp/beanbasetest.db"; // SQLite file name
  const USER = "";
  const PASS = "";

}

interface Selector {

  const MySQL = 1;
  const PgSQL = 1;

}