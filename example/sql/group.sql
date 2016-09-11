DROP TABLE IF EXISTS "group" CASCADE;

CREATE SEQUENCE "group_seq" RESTART;

CREATE TABLE "group"
(
	"ID" int NOT NULL DEFAULT nextval('group_seq'),
	"Name" varchar(255) NOT NULL,
	"Active" boolean NOT NULL DEFAULT false,
	CONSTRAINT "group_PK" PRIMARY KEY ("ID"),
	CONSTRAINT "group_UN_0" UNIQUE ("Name")
);

ALTER SEQUENCE "group_seq" OWNED BY "group"."ID";

COMMENT ON TABLE "group" IS 'Группа';

COMMENT ON COLUMN "group"."ID" IS 'Порядковые номер';
COMMENT ON COLUMN "group"."Name" IS 'Наименование';
COMMENT ON COLUMN "group"."Active" IS 'Активность';
