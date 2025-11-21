 PRAGMA foreign_keys=ON;

/*DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS booking;
DROP TABLE IF EXISTS ad;
DROP TABLE IF EXISTS message; */

CREATE TABLE IF NOT EXISTS user (
    username VARCHAR(25) PRIMARY KEY,
    name VARCHAR(25) NOT NULL,
    password VARCHAR(25) NOT NULL,
    email VARCHAR(25) NOT NULL,
    host_state VARCHAR(6) CONSTRAINT  is_host CHECK (host_state = 'true' OR host_state = 'false'),
    admin_state VARCHAR(6) CONSTRAINT is_admin CHECK (admin_state = 'true' OR admin_state = 'false'),
    profile_img_url VARCHAR(255),
    bio VARCHAR(512),
    seller_rating INT CONSTRAINT rate CHECK ("seller_rating" <= 5 AND "seller_rating" >= 0),
    is_admin INTEGER DEFAULT 0 CHECK (is_admin IN (0, 1)),
    is_host INTEGER DEFAULT 0 CHECK (is_host IN (0, 1))
);

CREATE TABLE IF NOT EXISTS ad (
    ad_id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(512),
    price DECIMAL(10, 2) CHECK (price > 0) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    seller VARCHAR(25) REFERENCES user(username) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
    small_desc VARCHAR(100),
    category INTEGER,
    location VARCHAR(25) NOT NULL CHECK (
        location IN ('Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora',
            'Faro', 'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo', 
            'Vila Real', 'Viseu')
    )
    
);

CREATE TABLE IF NOT EXISTS messages ( 
    message_id INTEGER PRIMARY KEY AUTOINCREMENT,
    message  TEXT,
    sent_at TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
    sender VARCHAR(25),
    conversation_id INTEGER REFERENCES conversations(conversation_id) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL
);

CREATE TABLE IF NOT EXISTS conversations (
    conversation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    ad_id INTEGER REFERENCES ad(ad_id) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
    guest VARCHAR(25),  
    seller VARCHAR(25) 

);

CREATE TABLE IF NOT EXISTS booking (
    booking_id INTEGER PRIMARY KEY AUTOINCREMENT,
    booked_at date,
    until_at date,
    guest VARCHAR(25) REFERENCES user(username) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
    ad_id INT REFERENCES ad(ad_id) ON DELETE CASCADE ON UPDATE CASCADE NOT NULL,
    state VARCHAR(12) NOT NULL DEFAULT 'Pending' CHECK (
    state IN ('Pending', 'Confirmed', 'Cancelled', 'Completed')
)
);

CREATE TABLE IF NOT EXISTS review (
    review_id INTEGER PRIMARY KEY AUTOINCREMENT,
    ad_id INT NOT NULL REFERENCES ad(ad_id) ON DELETE CASCADE,
    username VARCHAR(25)  NOT NULL REFERENCES user(username) ON DELETE CASCADE,
    rating INT CHECK(rating >= 0 AND rating <= 5),
    comment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS category (
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    categ_name VARCHAR(25)  NOT NULL UNIQUE
);


