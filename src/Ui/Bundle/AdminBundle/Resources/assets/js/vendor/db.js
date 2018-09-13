import Dexie from 'dexie';

let db = new Dexie('vimeet');
db.version(1).stores({
    identifiers: 'identifier,firstName,lastName,sheetTitle,participationType,checkin'
});

export default db;
