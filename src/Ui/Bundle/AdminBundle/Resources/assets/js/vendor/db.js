import Dexie from 'dexie';

let db = new Dexie('vimeet');
db.version(1).stores({
    qrCodePayloads: 'payload,firstName,lastName,sheetTitle'
});

export default db;
