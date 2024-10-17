const fs = require('fs');
const crypto = require('crypto');

const generateHashKey = () => {
    return crypto.randomBytes(256).toString('hex');
};

const jwtHashKey = generateHashKey();
const content = `JWT_HASH_KEY=${jwtHashKey}\n`;

fs.writeFile('.env', content, { flag: 'w' }, (err) => {
    if (err) {
        console.error('Failed to create .env: ', err);
    } else {
        console.log('.env successfully created with JWT_HASH_KEY.');
    }
});