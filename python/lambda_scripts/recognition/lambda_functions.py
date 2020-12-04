from __future__ import print_function

import boto3
from botocore.config import Config
from decimal import Decimal
import json
import urllib
import logging

rekognition = boto3.client('rekognition')

logger = logging.getLogger();
logger.setLevel(logging.INFO)

# --------------- Helper Functions to call Rekognition APIs ------------------


def detect_labels(bucket, key):
    response = rekognition.detect_labels(Image={"S3Object": {"Bucket": bucket, "Name": key}})
    return [{'Name': label_prediction['Name'], 'Confidence': Decimal(str(label_prediction['Confidence'])), 'response_json': json.dumps(label_prediction)} for label_prediction in response['Labels']]


def detect_text(bucket, key):
    response = rekognition.detect_text(Image={"S3Object": {"Bucket": bucket, "Name": key}})
    return [{'DetectedText': detected['DetectedText'], 'Confidence': Decimal(str(detected['Confidence'])), 'response_json': json.dumps(detected)} for detected in response['TextDetections']]


# --------------- Main handler ------------------


def lambda_handler(event, context):
    config = Config(connect_timeout=1, read_timeout=1, retries={'max_attempts': 1})
    
    bucket = event['Records'][0]['s3']['bucket']['name']
    key = urllib.parse.unquote_plus(event['Records'][0]['s3']['object']['key'])

    detections = detect_text(bucket, key)
    
    labels = detect_labels(bucket, key)
    
    config = Config(connect_timeout=1, read_timeout=1, retries={'max_attempts': 3})
    
    table = boto3.resource('dynamodb', config=config).Table(bucket)
    table.put_item(Item={'PK': key, 'Text:' detections, 'Labels:' labels})
    
    return

