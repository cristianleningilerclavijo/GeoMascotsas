// Obtener estos valores en Firebase Console -> Configuración del proyecto -> "Tus apps" -> app Web (</>).
// No son secretos: lo que protege los datos son las reglas de seguridad de Realtime Database.
const firebaseConfig = {
  apiKey: 'TU_API_KEY',
  authDomain: 'TU_PROYECTO.firebaseapp.com',
  databaseURL: 'https://TU_PROYECTO-default-rtdb.firebaseio.com',
  projectId: 'TU_PROYECTO',
  storageBucket: 'TU_PROYECTO.appspot.com',
  messagingSenderId: 'TU_SENDER_ID',
  appId: 'TU_APP_ID',
};

firebase.initializeApp(firebaseConfig);
const db = firebase.database();
