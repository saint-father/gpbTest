/*
Navicat PGSQL Data Transfer

Source Server         : pg-local
Source Server Version : 90504
Source Host           : 192.168.22.10:5432
Source Database       : gpb
Source Schema         : public

Target Server Type    : PGSQL
Target Server Version : 90504
File Encoding         : 65001

Date: 2017-02-03 12:57:03
*/


-- ----------------------------
-- Table structure for browsers_log
-- ----------------------------
DROP TABLE IF EXISTS "public"."browsers_log";
CREATE TABLE "public"."browsers_log" (
"ip" varchar(15) COLLATE "default" NOT NULL,
"browser" varchar(255) COLLATE "default" NOT NULL,
"os" varchar(255) COLLATE "default" NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Table structure for url_log
-- ----------------------------
DROP TABLE IF EXISTS "public"."url_log";
CREATE TABLE "public"."url_log" (
"ip" varchar(15) COLLATE "default" NOT NULL,
"created_at" timestamp(6) NOT NULL,
"url_from" varchar(255) COLLATE "default" NOT NULL,
"url_to" varchar(255) COLLATE "default" NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Alter Sequences Owned By 
-- ----------------------------

-- ----------------------------
-- Indexes structure for table browsers_log
-- ----------------------------
CREATE INDEX "browsers_ip" ON "public"."browsers_log" USING btree ("ip");

-- ----------------------------
-- Indexes structure for table url_log
-- ----------------------------
CREATE INDEX "ip" ON "public"."url_log" USING btree ("ip");
