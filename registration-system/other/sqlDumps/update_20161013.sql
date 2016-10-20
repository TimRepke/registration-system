-- this update merges the waitlist table with the bachelor table

-- first add necessary columns to the bachelor table
ALTER TABLE `bachelor`
  ADD `on_waitlist` int(11) NOT NULL DEFAULT 0,
  ADD `transferred` int(11) DEFAULT NULL;

-- first move those, that are not transferred yet
INSERT INTO `bachelor`
(`bachelor_id`, `fahrt_id`, `anm_time`, `forname`, `sirname`,
 `anday`, `abday`, `antyp`,`abtyp`, `pseudo`,
 `mehl`, `essen`,`public`, `virgin`, `studityp`, `comment`,
 `transferred`, `on_waitlist`, `version`,
 `paid`,  `repaid`, `backstepped`)
  SELECT `waitlist_id` AS `bachelor_id`,
    `fahrt_id`, `anm_time`, `forname`, `sirname`,
    `anday`, `abday`, `antyp`,`abtyp`, `pseudo`,
    `mehl`, `essen`,`public`, `virgin`, `studityp`, `comment`,
    NULL AS `transferred`, 1 AS `on_waitlist`, 0 AS `version`,
    NULL AS `paid`, NULL AS `repaid`, NULL AS `backstepped`
  FROM `waitlist` WHERE `transferred` IS NULL;

-- now update those, that were transferred already
UPDATE `bachelor` AS b, `waitlist` AS wl
SET
  b.`transferred`=wl.`transferred`,
  b.`on_waitlist`=1
WHERE
  wl.`waitlist_id` = b.`bachelor_id` AND
  wl.`fahrt_id` = b.`fahrt_id`;

-- remove deprecated waitlist table
DROP TABLE IF EXISTS `waitlist`;