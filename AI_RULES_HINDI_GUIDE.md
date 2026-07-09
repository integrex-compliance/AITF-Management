# AI Rules Feature - हिंदी गाइड

## सारांश
**masterCreator.php** पेज में सबसे नीचे AI Review Rules को edit करने का feature जोड़ दिया गया है। यह feature left side menu में नहीं दिखता, केवल masterCreator page में ही available है।

## कहाँ मिलेगा?
1. **masterCreator.php** page खोलें
2. सबसे नीचे scroll करें
3. "AI Review Rules" section दिखेगा

## Features

### ✅ नया AI Rule Add करना:
1. **"Add AI Rule"** button पर click करें
2. Form में details भरें:
   - **Rule Code** *(जरूरी)* - जैसे: ISO-DATE-001
   - **Title** *(जरूरी)* - Rule का नाम
   - **Category** - जैसे: Dates, Scope, Mandays
   - **Active** - Checkbox (default: checked)
   - **Rule Text** *(जरूरी)* - Rule की पूरी details
   - **Expected Value** - Additional information
3. **"Save Rule"** पर click करें
4. Rule list में नया rule दिख जाएगा

### ✅ Existing Rule को Edit करना:
1. Rule के सामने **pencil icon (✏️)** पर click करें
2. Form खुल जाएगा pre-filled data के साथ
3. Changes करें
4. **"Update Rule"** पर click करें

### ✅ Rule Delete करना:
1. Rule के सामने **trash icon (🗑️)** पर click करें
2. Confirmation dialog आएगा
3. **"Ok"** पर click करें
4. Rule delete हो जाएगा

### ✅ Rule को Activate/Deactivate करना:
1. Rule के सामने **"Activate"** या **"Deactivate"** button पर click करें
2. Page reload होगा
3. Status badge update हो जाएगा:
   - 🟢 **Green badge** = Active
   - ⚪ **Gray badge** = Inactive

## Validation (जांच)

### जरूरी Fields:
- ❌ **Rule Code** खाली नहीं हो सकता
- ❌ **Title** खाली नहीं हो सकता  
- ❌ **Rule Text** खाली नहीं हो सकता

अगर कोई जरूरी field खाली है, तो error message दिखेगा और rule save नहीं होगा।

## Important Notes

✔️ यह feature **left side menu से हटा दिया गया है** ✅  
✔️ अब केवल **masterCreator.php page के अंदर ही** manage होगा  
✔️ Page के **सबसे नीचे** है  
✔️ **Dark mode** में भी काम करता है  
✔️ **Username automatically record** होता है  
✔️ सभी changes **immediately reflect** होते हैं

⚠️ **ध्यान दें**: Left side menu में अब "AI REVIEW RULES" option नहीं दिखेगा। यह सिर्फ masterCreator page में manage होगा।

## अगर Problem आए तो:

1. Check करें कि database table `ai_review_rules` exist करता है
2. Browser console में errors check करें (F12 key)
3. `addNewServices.php` file ठीक से update हुई है या नहीं
4. PHP errors के लिए server logs देखें

---

**Feature successfully integrated! ✅**
