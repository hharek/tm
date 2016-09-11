DROP TABLE IF EXISTS "category" CASCADE;

CREATE SEQUENCE "category_seq" RESTART;

CREATE TABLE "category"
(
	"ID" int NOT NULL DEFAULT nextval('category_seq'),
	"Name" varchar(255) NOT NULL,
	"Url" varchar(255) NOT NULL,
	"Order" int NOT NULL DEFAULT currval('category_seq'),
	"Parent" int NULL,
	CONSTRAINT "category_PK" PRIMARY KEY ("ID"),
	CONSTRAINT "category_UN_Name" UNIQUE ("Name","Parent"),
	CONSTRAINT "category_UN_Url" UNIQUE ("Url","Parent"),
	CONSTRAINT "category_FK_Parent" FOREIGN KEY ("Parent")
		REFERENCES "category" ("ID") ON DELETE CASCADE

);

ALTER SEQUENCE "category_seq" OWNED BY "category"."ID";

COMMENT ON TABLE "category" IS 'Категория';

COMMENT ON COLUMN "category"."ID" IS 'Порядковый номер';
COMMENT ON COLUMN "category"."Name" IS 'Наименование';
COMMENT ON COLUMN "category"."Url" IS 'Урл';
COMMENT ON COLUMN "category"."Order" IS 'Сортировка';
COMMENT ON COLUMN "category"."Parent" IS 'Корень';


INSERT INTO "category" ("Name", "Url") VALUES ('Категория 1', 'категория-1');
INSERT INTO "category" ("Name", "Url") VALUES ('Категория 2', 'категория-2');
INSERT INTO "category" ("Name", "Url") VALUES ('Категория 3', 'категория-3');