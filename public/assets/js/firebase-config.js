const firebaseConfig = {
  apiKey: "AIzaSyBNsqjn3RY8TCpx8HRN3h8zz67nH8Rthf0",
  authDomain: "school-management-system-f4f6d.firebaseapp.com",
  projectId: "school-management-system-f4f6d",
  storageBucket: "school-management-system-f4f6d.firebasestorage.app",
  messagingSenderId: "670094395686",
  appId: "1:670094395686:web:7ba3bfe60f380f6db319e7",
  measurementId: "G-M9WQMN4YCC"
};

// Conditioned it; otherwise it will throw an error in serviceworker
if (typeof window != 'undefined')
    window.firebaseConfig = firebaseConfig; // Make it accessible by the importing script