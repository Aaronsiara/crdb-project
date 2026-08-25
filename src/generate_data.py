"""
generate_data.py

Generates a synthetic customer transaction dataset that mimics the shape of
retail / mobile-banking data (e.g. CRDB SimBanking-style usage). This is for
PROTOTYPING the segmentation pipeline before plugging in real (anonymized)
field-work data.

Run:
    python src/generate_data.py
Output:
    data/customers_synthetic.csv
"""

import numpy as np
import pandas as pd
from pathlib import Path

RNG = np.random.default_rng(42)
N_CUSTOMERS = 2000

REGIONS = [
    "Dar es Salaam", "Arusha", "Mwanza", "Dodoma",
    "Mbeya", "Zanzibar", "Morogoro", "Tanga"
]


def generate_customers(n=N_CUSTOMERS):
    """Create synthetic customers from a few latent behavioral archetypes,
    so the clustering step has real structure to recover."""

    archetype = RNG.choice(
        ["urban_digital", "rural_saver", "dormant", "high_freq_trader", "loan_active"],
        size=n,
        p=[0.30, 0.25, 0.15, 0.15, 0.15],
    )

    rows = []
    for i in range(n):
        a = archetype[i]

        if a == "urban_digital":
            txn_freq_month = RNG.normal(28, 6)
            avg_txn_value = RNG.normal(45000, 12000)
            mobile_login_freq = RNG.normal(24, 5)
            savings_balance = RNG.normal(600000, 200000)
            loan_active_flag = RNG.choice([0, 1], p=[0.8, 0.2])
            days_since_last_txn = RNG.normal(2, 1)

        elif a == "rural_saver":
            txn_freq_month = RNG.normal(6, 2)
            avg_txn_value = RNG.normal(15000, 5000)
            mobile_login_freq = RNG.normal(4, 2)
            savings_balance = RNG.normal(900000, 300000)
            loan_active_flag = RNG.choice([0, 1], p=[0.9, 0.1])
            days_since_last_txn = RNG.normal(10, 4)

        elif a == "dormant":
            txn_freq_month = RNG.normal(0.5, 0.5)
            avg_txn_value = RNG.normal(8000, 4000)
            mobile_login_freq = RNG.normal(0.3, 0.3)
            savings_balance = RNG.normal(50000, 30000)
            loan_active_flag = 0
            days_since_last_txn = RNG.normal(75, 20)

        elif a == "high_freq_trader":
            txn_freq_month = RNG.normal(60, 12)
            avg_txn_value = RNG.normal(90000, 25000)
            mobile_login_freq = RNG.normal(40, 8)
            savings_balance = RNG.normal(300000, 150000)
            loan_active_flag = RNG.choice([0, 1], p=[0.6, 0.4])
            days_since_last_txn = RNG.normal(1, 0.5)

        else:  # loan_active
            txn_freq_month = RNG.normal(15, 5)
            avg_txn_value = RNG.normal(35000, 10000)
            mobile_login_freq = RNG.normal(10, 4)
            savings_balance = RNG.normal(150000, 80000)
            loan_active_flag = 1
            days_since_last_txn = RNG.normal(5, 2)

        rows.append({
            "customer_id": f"CUS{i:05d}",
            "region": RNG.choice(REGIONS),
            "age": int(np.clip(RNG.normal(35, 12), 18, 75)),
            "txn_freq_month": max(0, txn_freq_month),
            "avg_txn_value_tzs": max(500, avg_txn_value),
            "mobile_login_freq_month": max(0, mobile_login_freq),
            "savings_balance_tzs": max(0, savings_balance),
            "loan_active_flag": loan_active_flag,
            "days_since_last_txn": max(0, days_since_last_txn),
            "_true_segment": a,  # kept only for validating the model; drop before training
        })

    return pd.DataFrame(rows)


if __name__ == "__main__":
    df = generate_customers()
    out_path = Path(__file__).resolve().parent.parent / "data" / "customers_synthetic.csv"
    out_path.parent.mkdir(exist_ok=True)
    df.to_csv(out_path, index=False)
    print(f"Wrote {len(df)} rows to {out_path}")
