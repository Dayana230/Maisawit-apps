import cv2
import pytesseract
import sys
from PIL import Image, ExifTags
import numpy as np
import os

# Palm oil keywords (lowercase for consistency)
palm_oil_keywords = [
    "palm", "palm oil", "palmate", "palmityl", "palmitic acid",
    "hydrogenated palm oil", "palm olein", "palm kernel oil",
    "sodium palm kernelate", "palm stearin", "elaeis guineensis",
    "palmitoyl", "palmitate", "palmitic"
]

def is_palm_oil_related(text):
    """Check entire block of text for palm-related keywords."""
    text = text.lower()
    found = [kw for kw in palm_oil_keywords if kw in text]
    return list(set(found))

def load_image_correct_orientation(path):
    image = Image.open(path)
    try:
        for orientation in ExifTags.TAGS.keys():
            if ExifTags.TAGS[orientation] == 'Orientation':
                break
        exif = dict(image._getexif().items())
        orientation_value = exif.get(orientation, None)

        if orientation_value == 3:
            image = image.rotate(180, expand=True)
        elif orientation_value == 6:
            image = image.rotate(270, expand=True)
        elif orientation_value == 8:
            image = image.rotate(90, expand=True)
    except Exception:
        pass

    return cv2.cvtColor(np.array(image), cv2.COLOR_RGB2BGR)

def analyze_image(file_path):
    image = load_image_correct_orientation(file_path)

    # Preprocess image for better OCR accuracy
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    gray = cv2.medianBlur(gray, 3)
    gray = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)[1]

    # OCR full block of text
    full_text = pytesseract.image_to_string(gray)
    found_keywords = is_palm_oil_related(full_text)

    # Annotate image for matched keywords (optional)
    data = pytesseract.image_to_data(gray, output_type=pytesseract.Output.DICT)
    for i in range(len(data['text'])):
        word = data['text'][i].strip().lower()
        for keyword in palm_oil_keywords:
            if keyword in word:
                (x, y, w, h) = (data['left'][i], data['top'][i], data['width'][i], data['height'][i])
                cv2.rectangle(image, (x, y), (x + w, y + h), (0, 255, 0), 2)
                cv2.putText(image, word, (x, y - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

    # Determine status
    if len(found_keywords) >= 2:
        status = "Certified Sustainable!"
        color = "green"
        message = "Certified Sustainable!\nThis product has OIL PALM ingredients"
    elif len(found_keywords) == 1:
        status = "Unknown Status!"
        color = "yellow"
        message = "Uncertain result!\nPossible OIL PALM ingredient detected"
    else:
        status = "Unsustainable!"
        color = "red"
        message = "Unsustainable!\nNo OIL PALM ingredient detected"

    # Save result image
    output_path = os.path.splitext(file_path)[0] + "_result.jpg"
    cv2.imwrite(output_path, image)

    # Output
    print(f"STATUS: {status}")
    print(f"COLOR: {color}")
    print(f"MESSAGE: {message}")
    print("KEYWORDS: " + ", ".join(found_keywords))
    print(f"RESULT_IMAGE: {os.path.basename(output_path)}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python3 analyze.py <image_path>")
    else:
        analyze_image(sys.argv[1])
