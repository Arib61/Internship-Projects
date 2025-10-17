# oracle_pool.py
import cx_Oracle

class OraclePool:
    _pool = None

    @classmethod
    def get_pool(cls):
        if cls._pool is None:
            dsn = cx_Oracle.makedsn("192.168.13.61", 1521, service_name="ORCL")
            # ⚠️ Tu peux ajuster min/max selon la charge attendue
            cls._pool = cx_Oracle.SessionPool(
                "GABON_PROD", "gaboit2", dsn,
                min=1, max=5, increment=1, threaded=True, encoding="UTF-8"
            )
        return cls._pool

    @classmethod
    def acquire_connection(cls):
        pool = cls.get_pool()
        return pool.acquire()
