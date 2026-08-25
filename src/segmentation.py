"""
segmentation.py

Core pipeline: load customer data -> scale features -> PCA for
dimensionality reduction / visualization -> KMeans clustering ->
profile each resulting segment.

Run:
    python src/segmentation.py
Outputs:
    outputs/pca_clusters.png
    outputs/elbow_plot.png
    outputs/segment_profiles.csv
"""

import argparse
from pathlib import Path

import matplotlib.pyplot as plt
import pandas as pd
import seaborn as sns
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler

FEATURES = [
    "txn_freq_month",
    "avg_txn_value_tzs",
    "mobile_login_freq_month",
    "savings_balance_tzs",
    "loan_active_flag",
    "days_since_last_txn",
]

ROOT = Path(__file__).resolve().parent.parent
DATA_PATH = ROOT / "data" / "customers_synthetic.csv"
OUT_DIR = ROOT / "outputs"


def load_data(path: Path = DATA_PATH) -> pd.DataFrame:
    df = pd.read_csv(path)
    missing = [f for f in FEATURES if f not in df.columns]
    if missing:
        raise ValueError(f"Missing expected columns: {missing}")
    return df


def scale_features(df: pd.DataFrame):
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(df[FEATURES])
    return X_scaled, scaler


def run_pca(X_scaled, n_components: int = 2):
    pca = PCA(n_components=n_components, random_state=42)
    X_pca = pca.fit_transform(X_scaled)
    explained = pca.explained_variance_ratio_
    print(f"Explained variance by component: {explained}")
    print(f"Total explained variance ({n_components} components): {explained.sum():.2%}")
    return X_pca, pca


def elbow_plot(X_scaled, max_k: int = 10, save_path: Path = None):
    inertias = []
    ks = range(1, max_k + 1)
    for k in ks:
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        km.fit(X_scaled)
        inertias.append(km.inertia_)

    plt.figure(figsize=(7, 5))
    plt.plot(list(ks), inertias, marker="o")
    plt.xlabel("Number of clusters (k)")
    plt.ylabel("Inertia")
    plt.title("Elbow Method for Optimal k")
    plt.tight_layout()
    if save_path:
        plt.savefig(save_path, dpi=150)
        print(f"Saved elbow plot to {save_path}")
    plt.close()


def run_kmeans(X_scaled, k: int = 5):
    km = KMeans(n_clusters=k, random_state=42, n_init=10)
    labels = km.fit_predict(X_scaled)
    return labels, km


def profile_segments(df: pd.DataFrame, labels) -> pd.DataFrame:
    df = df.copy()
    df["segment"] = labels
    profile = df.groupby("segment")[FEATURES].mean().round(1)
    profile["n_customers"] = df.groupby("segment").size()
    return profile.sort_values("n_customers", ascending=False)


def plot_pca_clusters(X_pca, labels, save_path: Path = None):
    plt.figure(figsize=(8, 6))
    sns.scatterplot(
        x=X_pca[:, 0], y=X_pca[:, 1],
        hue=labels, palette="tab10", s=40, alpha=0.8
    )
    plt.xlabel("PC1")
    plt.ylabel("PC2")
    plt.title("Customer Segments (PCA-reduced view)")
    plt.legend(title="Segment")
    plt.tight_layout()
    if save_path:
        plt.savefig(save_path, dpi=150)
        print(f"Saved PCA cluster plot to {save_path}")
    plt.close()


def main(k: int, max_k: int):
    OUT_DIR.mkdir(exist_ok=True)

    df = load_data()
    X_scaled, _ = scale_features(df)

    elbow_plot(X_scaled, max_k=max_k, save_path=OUT_DIR / "elbow_plot.png")

    X_pca, _ = run_pca(X_scaled, n_components=2)
    labels, _ = run_kmeans(X_scaled, k=k)

    plot_pca_clusters(X_pca, labels, save_path=OUT_DIR / "pca_clusters.png")

    profile = profile_segments(df, labels)
    profile.to_csv(OUT_DIR / "segment_profiles.csv")
    print("\nSegment profiles:\n")
    print(profile)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Run customer segmentation pipeline.")
    parser.add_argument("--k", type=int, default=5, help="Number of clusters for KMeans")
    parser.add_argument("--max-k", type=int, default=10, help="Max k to test in elbow plot")
    args = parser.parse_args()
    main(k=args.k, max_k=args.max_k)
