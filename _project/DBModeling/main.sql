-- MySQL Script generated by MySQL Workbench
-- Tue Jan 22 21:54:51 2019
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`r_node_subjects`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`r_node_subjects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `node_title` VARCHAR(480) NOT NULL COMMENT '节点标题',
  `sequence` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `parent_node_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级节点id',
  `is_cq` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是选择题考点',
  `is_saq` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是简答题考点',
  `subjects` TEXT NOT NULL DEFAULT '' COMMENT '主体（内容）',
  `status` TINYINT(1) NOT NULL COMMENT '状态：-1，已删除；0，待审核；1，审核通过',
  `update_time` INT UNSIGNED NOT NULL COMMENT '更新时间',
  `create_time` INT UNSIGNED NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '笔记';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
