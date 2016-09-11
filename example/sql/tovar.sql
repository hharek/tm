DROP TABLE IF EXISTS "tovar" CASCADE;

CREATE SEQUENCE "tovar_seq" RESTART;

CREATE TABLE "tovar"
(
	"ID" int NOT NULL DEFAULT nextval('tovar_seq'),
	"Name" varchar(255) NOT NULL,
	"Url" varchar(255) NOT NULL,
	"Content" text NULL,
	"Category_ID" int NOT NULL,
	"Active" boolean NOT NULL DEFAULT false,
	CONSTRAINT "tovar_PK" PRIMARY KEY ("ID"),
	CONSTRAINT "tovar_UN_Name" UNIQUE ("Name","Category_ID"),
	CONSTRAINT "tovar_UN_Url" UNIQUE ("Url","Category_ID"),
	CONSTRAINT "tovar_FK_Category_ID" FOREIGN KEY ("Category_ID")
		REFERENCES "category" ("ID") ON DELETE CASCADE

);

ALTER SEQUENCE "tovar_seq" OWNED BY "tovar"."ID";

COMMENT ON TABLE "tovar" IS 'Товар';

COMMENT ON COLUMN "tovar"."ID" IS 'Порядковый номер';
COMMENT ON COLUMN "tovar"."Name" IS 'Наименование';
COMMENT ON COLUMN "tovar"."Url" IS 'Урл';
COMMENT ON COLUMN "tovar"."Content" IS 'Содержание';
COMMENT ON COLUMN "tovar"."Category_ID" IS 'Привязка к категории';
COMMENT ON COLUMN "tovar"."Active" IS 'Активность';
