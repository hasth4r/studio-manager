import os
import re

views_dir = r"c:\xampp\htdocs\eso8manager_v0.0.1\app\Views"

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace bg-ytBlue text-black ... hover:bg-blue-300
    # Pattern to match bg-ytBlue and text-black and hover:bg-blue-300 anywhere in class string
    # Easiest way: just string replace
    
    new_content = content
    # Replace standard parts
    new_content = new_content.replace('bg-ytBlue text-black', 'bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40')
    new_content = new_content.replace('hover:bg-blue-300', 'hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]')
    
    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.php'):
            process_file(os.path.join(root, file))
