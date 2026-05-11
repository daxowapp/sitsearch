with open('/Users/darwish/Dev/sitsearch/wp-content/plugins/sit-program-recommender/assets/js/frontend.js', 'r') as f:
    text = f.read()

text = text.replace(r'\`', '`')
text = text.replace(r'\${', '${')
text = text.replace(r'\'', "'")
text = text.replace(r'\"', '"')

with open('/Users/darwish/Dev/sitsearch/wp-content/plugins/sit-program-recommender/assets/js/frontend.js', 'w') as f:
    f.write(text)
print("Fix applied.")
