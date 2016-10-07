DROP TABLE IF EXISTS "product" CASCADE;

CREATE SEQUENCE "product_seq" RESTART;

CREATE TABLE "product"
(
	"ID" int NOT NULL DEFAULT nextval('product_seq'),
	"Name" varchar(255) NOT NULL,
	"Url" varchar(255) NOT NULL,
	"Content" text NULL,
	"Price" numeric(10,2) NOT NULL,
	"Category_ID" int NOT NULL,
	"Sort" int NOT NULL DEFAULT currval('product_seq'),
	"Active" boolean NOT NULL DEFAULT true,
	CONSTRAINT "product_PK" PRIMARY KEY ("ID"),
	CONSTRAINT "product_product_FK_Category_ID" FOREIGN KEY ("Category_ID")
		REFERENCES "category" ("ID") ON DELETE CASCADE
);

ALTER SEQUENCE "product_seq" OWNED BY "product"."ID";

CREATE UNIQUE INDEX "product_UN1" ON "product" ("Name", "Category_ID");
CREATE UNIQUE INDEX "product_UN2" ON "product" ("Url", "Category_ID");

COMMENT ON TABLE "product" IS 'Товар';

COMMENT ON COLUMN "product"."ID" IS 'Порядковый номер';
COMMENT ON COLUMN "product"."Name" IS 'Наименование';
COMMENT ON COLUMN "product"."Url" IS 'Урл';
COMMENT ON COLUMN "product"."Content" IS 'Содержание';
COMMENT ON COLUMN "product"."Price" IS 'Цена';
COMMENT ON COLUMN "product"."Category_ID" IS 'Привязка к категории';
COMMENT ON COLUMN "product"."Sort" IS 'Сортировка';
COMMENT ON COLUMN "product"."Active" IS 'Активность';
