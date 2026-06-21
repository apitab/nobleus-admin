import time
import json
import requests
import mysql.connector
from requests.auth import HTTPBasicAuth

# Database configuration
DB_CONFIG = {
    'host': 'your_database_host',
    'user': 'your_database_user',
    'password': 'your_database_password',
    'database': 'your_database_name'
}

# REST API configuration
API_URL = 'https://your-api-endpoint.com/transactions'
API_USERNAME = 'your_api_username'
API_PASSWORD = 'your_api_password'

# Interval in seconds between each polling cycle
POLL_INTERVAL = 10

def get_pending_transactions(connection):
    """Retrieve pending transactions from the database."""
    cursor = connection.cursor(dictionary=True)
    query = "SELECT * FROM transactions WHERE status = 'PENDING'"
    cursor.execute(query)
    transactions = cursor.fetchall()
    cursor.close()
    return transactions

def update_transaction_status(connection, transaction_id, status):
    """Update the status of a transaction."""
    cursor = connection.cursor()
    query = "UPDATE transactions SET status = %s WHERE id = %s"
    cursor.execute(query, (status, transaction_id))
    connection.commit()
    cursor.close()

def push_transaction(transaction):
    """Push transaction to the REST API."""
    try:
        response = requests.post(
            API_URL,
            auth=HTTPBasicAuth(API_USERNAME, API_PASSWORD),
            headers={'Content-Type': 'application/json'},
            data=json.dumps(transaction)
        )
        response.raise_for_status()  # Will raise an HTTPError for bad responses
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error pushing transaction {transaction['id']}: {e}")
        return None

def main():
    """Main daemon loop."""
    # Connect to the database
    connection = mysql.connector.connect(**DB_CONFIG)
    
    try:
        while True:
            print("Checking for pending transactions...")
            transactions = get_pending_transactions(connection)
            
            for transaction in transactions:
                print(f"Pushing transaction ID {transaction['id']} to API...")
                response = push_transaction(transaction)
                
                if response:
                    print(f"Transaction ID {transaction['id']} pushed successfully.")
                    update_transaction_status(connection, transaction['id'], 'SUCCESS')
                else:
                    print(f"Failed to push transaction ID {transaction['id']}.")
                    update_transaction_status(connection, transaction['id'], 'FAILED')
            
            # Wait before polling again
            time.sleep(POLL_INTERVAL)
    except KeyboardInterrupt:
        print("Daemon stopped.")
    finally:
        connection.close()

if __name__ == "__main__":
    main()